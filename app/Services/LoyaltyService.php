<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\LoyaltyAccount;
use App\Models\LoyaltyTier;
use App\Models\LoyaltyTierReward;
use App\Models\LoyaltyTransaction;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Refund;
use App\Models\SavedCoupon;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LoyaltyService
{
    public function getOrCreateAccount(User $user): LoyaltyAccount
    {
        return DB::transaction(function () use ($user): LoyaltyAccount {
            $account = LoyaltyAccount::query()
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if ($account) {
                $this->syncTier($account);

                return $account->fresh([
                    'tier',
                    'highestTier',
                ]);
            }

            $initialTier = $this->findQualifiedTier(0, 0);

            $account = LoyaltyAccount::query()->create([
                'user_id' => $user->id,
                'tier_id' => $initialTier?->id,
                'highest_tier_id' => $initialTier?->id,
                'available_points' => 0,
                'pending_points' => 0,
                'lifetime_earned_points' => 0,
                'lifetime_redeemed_points' => 0,
                'lifetime_spending' => 0,
                'tier_started_at' => $initialTier ? now() : null,
                'tier_expires_at' => null,
                'last_completed_order_at' => null,
                'inactive_downgraded_at' => null,
            ]);

            return $account->load([
                'tier',
                'highestTier',
            ]);
        });
    }

    /**
     * Đồng bộ hạng cao nhất theo tổng chi tiêu và tổng điểm từng kiếm được.
     * Nếu tài khoản đang bị hạ hạng vì không hoạt động, chỉ cập nhật hạng cao
     * nhất đã đạt, không tự nâng hạng hiện tại cho đến khi có đơn hoàn thành.
     */
    public function syncTier(LoyaltyAccount $account): LoyaltyAccount
    {
        $account->loadMissing(['tier', 'highestTier', 'user']);

        $qualifiedTier = $this->findQualifiedTier(
            (float) $account->lifetime_spending,
            (int) $account->lifetime_earned_points
        );

        $oldHighest = $account->highestTier;
        $highestTier = $this->higherTier($oldHighest, $qualifiedTier);

        $updates = [];

        if ((int) $account->highest_tier_id !== (int) $highestTier?->id) {
            $updates['highest_tier_id'] = $highestTier?->id;
        }

        if ($account->inactive_downgraded_at === null) {
            if ((int) $account->tier_id !== (int) $qualifiedTier?->id) {
                $updates['tier_id'] = $qualifiedTier?->id;
                $updates['tier_started_at'] = $qualifiedTier ? now() : null;
                $updates['tier_expires_at'] = null;
            }
        }

        if ($updates !== []) {
            $account->update($updates);
        }

        if (
            $highestTier
            && (!$oldHighest || $highestTier->sort_order > $oldHighest->sort_order)
        ) {
            $this->awardTierReward($account->fresh(), $highestTier);
            $this->notifyTierUpgrade($account->user, $highestTier);
        }

        return $account->fresh([
            'tier',
            'highestTier',
        ]);
    }

    public function findQualifiedTier(float $spending, int $earnedPoints): ?LoyaltyTier
    {
        return LoyaltyTier::query()
            ->active()
            ->get()
            ->filter(fn (LoyaltyTier $tier): bool => $tier->qualifies($spending, $earnedPoints))
            ->sortByDesc(fn (LoyaltyTier $tier): array => [
                (int) $tier->sort_order,
                (float) $tier->minimum_spending,
                (int) $tier->minimum_points,
            ])
            ->first();
    }

    public function getNextTier(LoyaltyAccount $account): ?LoyaltyTier
    {
        $comparisonTier = $account->highestTier ?: $account->tier;
        $currentSortOrder = $comparisonTier
            ? (int) $comparisonTier->sort_order
            : -1;

        return LoyaltyTier::query()
            ->where('status', true)
            ->whereNull('deleted_at')
            ->where('sort_order', '>', $currentSortOrder)
            ->orderBy('sort_order')
            ->orderBy('minimum_spending')
            ->first();
    }

    public function getTierProgress(
        LoyaltyAccount $account,
        ?LoyaltyTier $nextTier
    ): array {
        if (!$nextTier) {
            return [
                'spending_required' => 0,
                'spending_remaining' => 0,
                'points_required' => 0,
                'points_remaining' => 0,
                'spending_percentage' => 100,
                'points_percentage' => 100,
                'overall_percentage' => 100,
            ];
        }

        $spending = (float) $account->lifetime_spending;
        $earnedPoints = (int) $account->lifetime_earned_points;
        $spendingRequired = (float) $nextTier->minimum_spending;
        $pointsRequired = (int) $nextTier->minimum_points;

        $spendingPercentage = $spendingRequired > 0
            ? min(100, round(($spending / $spendingRequired) * 100, 1))
            : 100;

        $pointsPercentage = $pointsRequired > 0
            ? min(100, round(($earnedPoints / $pointsRequired) * 100, 1))
            : 100;

        return [
            'spending_required' => $spendingRequired,
            'spending_remaining' => max(0, $spendingRequired - $spending),
            'points_required' => $pointsRequired,
            'points_remaining' => max(0, $pointsRequired - $earnedPoints),
            'spending_percentage' => $spendingPercentage,
            'points_percentage' => $pointsPercentage,
            'overall_percentage' => min($spendingPercentage, $pointsPercentage),
        ];
    }

    /**
     * Cộng điểm khi đơn hoàn thành. Nếu trước đó khách bị hạ hạng do không
     * hoạt động, đơn hoàn thành bất kỳ sẽ khôi phục hạng cao nhất đã đạt trước
     * khi tính hệ số điểm cho đơn này.
     */
    public function awardCompletedOrder(Order $order, ?int $createdBy = null): void
    {
        DB::transaction(function () use ($order, $createdBy): void {
            $lockedOrder = Order::query()
                ->with('user')
                ->lockForUpdate()
                ->findOrFail($order->id);

            if (
                $lockedOrder->order_status !== 'completed'
                || !$lockedOrder->user_id
                || !$lockedOrder->user
            ) {
                return;
            }

            $alreadyAwarded = LoyaltyTransaction::query()
                ->where('reference_type', Order::class)
                ->where('reference_id', $lockedOrder->id)
                ->where('type', 'earn')
                ->where('status', 'completed')
                ->exists();

            if ($alreadyAwarded) {
                return;
            }

            $account = LoyaltyAccount::query()
                ->where('user_id', $lockedOrder->user_id)
                ->lockForUpdate()
                ->first();

            if (!$account) {
                $this->getOrCreateAccount($lockedOrder->user);
                $account = LoyaltyAccount::query()
                    ->where('user_id', $lockedOrder->user_id)
                    ->lockForUpdate()
                    ->firstOrFail();
            }

            $this->restoreHighestTierAfterPurchase($account);
            $account = $account->fresh(['tier', 'highestTier']);

            $eligibleAmount = max(0, (float) $lockedOrder->total_amount);

            if ($eligibleAmount <= 0) {
                return;
            }

            $multiplier = max(0, (float) ($account->tier?->point_multiplier ?? 1));
            $earnedPoints = (int) floor(($eligibleAmount / 1000) * $multiplier);

            if ($earnedPoints <= 0) {
                return;
            }

            $balanceBefore = (int) $account->available_points;
            $balanceAfter = $balanceBefore + $earnedPoints;

            $account->update([
                'available_points' => $balanceAfter,
                'lifetime_earned_points' =>
                    (int) $account->lifetime_earned_points + $earnedPoints,
                'lifetime_spending' =>
                    (float) $account->lifetime_spending + $eligibleAmount,
                'last_completed_order_at' => $lockedOrder->completed_at ?? now(),
            ]);

            LoyaltyTransaction::query()->create([
                'loyalty_account_id' => $account->id,
                'order_id' => $lockedOrder->id,
                'type' => 'earn',
                'points' => $earnedPoints,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'monetary_value' => $eligibleAmount,
                'status' => 'completed',
                'reference_type' => Order::class,
                'reference_id' => $lockedOrder->id,
                'description' => sprintf(
                    'Cộng %d điểm từ đơn hàng %s (hệ số x%s).',
                    $earnedPoints,
                    $lockedOrder->order_code,
                    rtrim(rtrim(number_format($multiplier, 2, '.', ''), '0'), '.')
                ),
                'available_at' => now(),
                'expires_at' => null,
                'created_by' => $createdBy,
            ]);

            $this->syncTier($account->fresh());
        }, 3);
    }

    public function restoreHighestTierAfterPurchase(LoyaltyAccount $account): LoyaltyAccount
    {
        $account->loadMissing(['tier', 'highestTier', 'user']);

        if ($account->inactive_downgraded_at === null) {
            return $account;
        }

        $restoreTier = $account->highestTier
            ?: $this->findQualifiedTier(
                (float) $account->lifetime_spending,
                (int) $account->lifetime_earned_points
            );

        $oldTier = $account->tier;

        $account->update([
            'tier_id' => $restoreTier?->id,
            'tier_started_at' => $restoreTier ? now() : null,
            'tier_expires_at' => null,
            'inactive_downgraded_at' => null,
            'last_completed_order_at' => now(),
        ]);

        if ($restoreTier && (int) $oldTier?->id !== (int) $restoreTier->id) {
            $this->createTierTransaction(
                $account,
                'Khôi phục hạng ' . $restoreTier->name
                    . ' sau khi khách hoàn thành đơn hàng mới.'
            );

            $this->createNotification(
                $account->user,
                'Hạng thành viên đã được khôi phục',
                'Đơn hàng mới đã hoàn thành. Hệ thống đã khôi phục hạng '
                    . $restoreTier->name . ' — hạng cao nhất bạn từng đạt.',
                'promotion'
            );
        }

        return $account->fresh(['tier', 'highestTier']);
    }

    /**
     * Hạ đúng một hạng cho mỗi đợt không hoạt động. Không tiếp tục hạ thêm
     * ở các lần chạy sau; chỉ khi khách mua lại thì cờ hạ hạng mới được xóa.
     */
    public function downgradeInactiveAccounts(?int $days = null): int
    {
        $days ??= max(1, (int) config('loyalty.inactivity_days', 90));
        $cutoff = now()->subDays($days);
        $count = 0;

        LoyaltyAccount::query()
            ->with(['tier', 'highestTier', 'user'])
            ->whereNull('inactive_downgraded_at')
            ->whereNotNull('last_completed_order_at')
            ->where('last_completed_order_at', '<=', $cutoff)
            ->orderBy('id')
            ->chunkById(100, function ($accounts) use (&$count, $days): void {
                foreach ($accounts as $account) {
                    DB::transaction(function () use ($account, &$count, $days): void {
                        $locked = LoyaltyAccount::query()
                            ->with(['tier', 'highestTier', 'user'])
                            ->lockForUpdate()
                            ->find($account->id);

                        if (!$locked || $locked->inactive_downgraded_at !== null) {
                            return;
                        }

                        $lowerTier = $this->getPreviousTier($locked->tier);

                        if (!$lowerTier || (int) $lowerTier->id === (int) $locked->tier_id) {
                            return;
                        }

                        $oldTierName = $locked->tier?->name ?? 'Thành viên';

                        $locked->update([
                            'tier_id' => $lowerTier->id,
                            'tier_started_at' => now(),
                            'tier_expires_at' => null,
                            'inactive_downgraded_at' => now(),
                        ]);

                        $this->createTierTransaction(
                            $locked,
                            "Hạ 1 hạng từ {$oldTierName} xuống {$lowerTier->name} do không có đơn hoàn thành trong {$days} ngày."
                        );

                        $this->createNotification(
                            $locked->user,
                            'Hạng thành viên đã được điều chỉnh',
                            "Do {$days} ngày không có đơn hoàn thành, hạng của bạn tạm thời giảm xuống {$lowerTier->name}. Chỉ cần hoàn thành một đơn bất kỳ để khôi phục hạng cao nhất đã đạt.",
                            'promotion'
                        );

                        $count++;
                    }, 3);
                }
            });

        return $count;
    }

    public function reverseCompletedRefund(Refund $refund, ?int $createdBy = null): void
    {
        DB::transaction(function () use ($refund, $createdBy): void {
            $lockedRefund = Refund::query()
                ->with('order')
                ->lockForUpdate()
                ->findOrFail($refund->id);

            if (
                $lockedRefund->status !== 'completed'
                || !$lockedRefund->order
                || !$lockedRefund->order->user_id
            ) {
                return;
            }

            $alreadyProcessed = LoyaltyTransaction::query()
                ->where('reference_type', Refund::class)
                ->where('reference_id', $lockedRefund->id)
                ->where('type', 'refund')
                ->where('status', 'completed')
                ->exists();

            if ($alreadyProcessed) {
                return;
            }

            $account = LoyaltyAccount::query()
                ->where('user_id', $lockedRefund->order->user_id)
                ->lockForUpdate()
                ->first();

            if (!$account) {
                return;
            }

            $orderAmount = max(0, (float) $lockedRefund->order->total_amount);
            $refundAmount = min($orderAmount, max(0, (float) $lockedRefund->amount));

            if ($refundAmount <= 0) {
                return;
            }

            $earnedPoints = (int) LoyaltyTransaction::query()
                ->where('loyalty_account_id', $account->id)
                ->where('order_id', $lockedRefund->order_id)
                ->where('type', 'earn')
                ->where('status', 'completed')
                ->where('points', '>', 0)
                ->sum('points');

            $pointsToReverse = $orderAmount > 0
                ? (int) floor($earnedPoints * ($refundAmount / $orderAmount))
                : 0;

            $pointsToReverse = min($pointsToReverse, (int) $account->available_points);
            $balanceBefore = (int) $account->available_points;
            $balanceAfter = max(0, $balanceBefore - $pointsToReverse);

            $account->update([
                'available_points' => $balanceAfter,
                'lifetime_earned_points' => max(
                    0,
                    (int) $account->lifetime_earned_points - $pointsToReverse
                ),
                'lifetime_spending' => max(
                    0,
                    (float) $account->lifetime_spending - $refundAmount
                ),
            ]);

            LoyaltyTransaction::query()->create([
                'loyalty_account_id' => $account->id,
                'order_id' => $lockedRefund->order_id,
                'type' => 'refund',
                'points' => -$pointsToReverse,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'monetary_value' => $refundAmount,
                'status' => 'completed',
                'reference_type' => Refund::class,
                'reference_id' => $lockedRefund->id,
                'description' => sprintf(
                    'Thu hồi điểm do hoàn tiền %s cho đơn %s.',
                    $lockedRefund->refund_code,
                    $lockedRefund->order->order_code
                ),
                'available_at' => now(),
                'expires_at' => null,
                'created_by' => $createdBy,
            ]);

            $this->syncTier($account->fresh());
        }, 3);
    }

    private function awardTierReward(
        LoyaltyAccount $account,
        LoyaltyTier $tier
    ): void {
        if (!$tier->reward_enabled || !$tier->reward_discount_type) {
            return;
        }

        $alreadyAwarded = LoyaltyTierReward::query()
            ->where('loyalty_account_id', $account->id)
            ->where('loyalty_tier_id', $tier->id)
            ->exists();

        if ($alreadyAwarded) {
            return;
        }

        $coupon = Coupon::query()->create([
            'code' => $this->makeRewardCouponCode($tier, $account->user_id),
            'name' => $tier->reward_name ?: 'Quà thăng hạng ' . $tier->name,
            'description' => $tier->reward_description
                ?: 'Voucher dành riêng cho khách vừa đạt hạng ' . $tier->name . '.',
            'discount_type' => $tier->reward_discount_type,
            'discount_value' => (float) ($tier->reward_discount_value ?? 0),
            'maximum_discount' => $tier->reward_maximum_discount,
            'minimum_order_amount' => (float) $tier->reward_minimum_order_amount,
            'usage_limit' => 1,
            'usage_limit_per_user' => 1,
            'used_count' => 0,
            'first_order_only' => false,
            'is_public' => false,
            'status' => true,
            'start_at' => now(),
            'end_at' => now()->addDays(max(1, (int) $tier->reward_valid_days)),
            'created_by' => null,
        ]);

        $coupon->users()->syncWithoutDetaching([$account->user_id]);

        SavedCoupon::query()->firstOrCreate(
            [
                'user_id' => $account->user_id,
                'coupon_id' => $coupon->id,
            ],
            [
                'saved_at' => now(),
            ]
        );

        LoyaltyTierReward::query()->create([
            'loyalty_account_id' => $account->id,
            'loyalty_tier_id' => $tier->id,
            'coupon_id' => $coupon->id,
            'awarded_at' => now(),
        ]);

        $this->createNotification(
            $account->user,
            'Bạn nhận được voucher thăng hạng',
            'Voucher ' . $coupon->code . ' đã được thêm vào Ví voucher của bạn.',
            'promotion',
            route('account.coupons.index')
        );
    }

    private function notifyTierUpgrade(?User $user, LoyaltyTier $tier): void
    {
        if (!$user) {
            return;
        }

        $this->createNotification(
            $user,
            'Chúc mừng bạn đã thăng hạng ' . $tier->name,
            'Bạn được hưởng hệ số tích điểm x'
                . rtrim(rtrim(number_format((float) $tier->point_multiplier, 2, '.', ''), '0'), '.')
                . ' và các ưu đãi riêng của hạng này.',
            'promotion',
            route('account.loyalty.index')
        );
    }

    private function createNotification(
        ?User $user,
        string $title,
        string $message,
        string $category = 'promotion',
        ?string $actionUrl = null
    ): void {
        if (!$user) {
            return;
        }

        Notification::query()->create([
            'type' => 'loyalty',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'title' => $title,
            'message' => $message,
            'category' => $category,
            'action_url' => $actionUrl,
            'priority' => 'normal',
            'data' => [],
        ]);
    }

    private function createTierTransaction(LoyaltyAccount $account, string $description): void
    {
        LoyaltyTransaction::query()->create([
            'loyalty_account_id' => $account->id,
            'order_id' => null,
            'type' => 'adjust',
            'points' => 0,
            'balance_before' => (int) $account->available_points,
            'balance_after' => (int) $account->available_points,
            'monetary_value' => 0,
            'status' => 'completed',
            'reference_type' => 'tier_change',
            'reference_id' => $account->tier_id,
            'description' => $description,
            'available_at' => now(),
            'expires_at' => null,
            'created_by' => null,
        ]);
    }

    private function getPreviousTier(?LoyaltyTier $tier): ?LoyaltyTier
    {
        if (!$tier) {
            return null;
        }

        return LoyaltyTier::query()
            ->active()
            ->where('sort_order', '<', (int) $tier->sort_order)
            ->orderByDesc('sort_order')
            ->orderByDesc('minimum_spending')
            ->first();
    }

    private function higherTier(
        ?LoyaltyTier $first,
        ?LoyaltyTier $second
    ): ?LoyaltyTier {
        if (!$first) {
            return $second;
        }

        if (!$second) {
            return $first;
        }

        return $second->sort_order > $first->sort_order
            ? $second
            : $first;
    }

    private function makeRewardCouponCode(LoyaltyTier $tier, int $userId): string
    {
        do {
            $prefix = Str::limit(
                strtoupper(Str::slug($tier->code, '')),
                28,
                ''
            );

            $code = $prefix
                . '-' . $userId . '-' . Str::upper(Str::random(6));
        } while (Coupon::query()->where('code', $code)->exists());

        return $code;
    }
}
