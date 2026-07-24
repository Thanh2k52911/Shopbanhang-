<?php

namespace App\Observers;

use App\Models\LoyaltyAccount;
use App\Models\LoyaltyTier;
use App\Services\ClientNotificationService;

class LoyaltyAccountObserver
{
    public function __construct(private readonly ClientNotificationService $notifications) {}

    public function updated(LoyaltyAccount $account): void
    {
        if (! $account->wasChanged('tier_id')) return;

        $oldTier = LoyaltyTier::query()->find($account->getOriginal('tier_id'));
        $newTier = LoyaltyTier::query()->find($account->tier_id);
        if (! $newTier) return;

        $account->loadMissing('user');
        $oldOrder = (int) ($oldTier?->sort_order ?? -1);
        $newOrder = (int) $newTier->sort_order;
        $highestId = $account->getAttribute('highest_tier_id');

        if ($newOrder > $oldOrder) {
            $restored = $highestId && (int) $highestId === (int) $newTier->id && $oldTier;
            $type = $restored ? 'loyalty.restored' : 'loyalty.upgraded';
            $title = $restored ? 'Hạng thành viên đã được khôi phục' : 'Chúc mừng bạn đã thăng hạng';
            $message = $restored
                ? sprintf('Hạng của bạn đã được khôi phục về %s sau khi mua hàng.', $newTier->name)
                : sprintf('Bạn đã đạt hạng %s và được mở khóa các quyền lợi mới.', $newTier->name);
        } else {
            $type = 'loyalty.downgraded';
            $title = 'Hạng thành viên đã thay đổi';
            $message = sprintf('Do lâu không có đơn hoàn thành, hạng của bạn đã chuyển từ %s xuống %s.', $oldTier?->name ?? 'hạng trước', $newTier->name);
        }

        $this->notifications->safely(fn () => $this->notifications->send(
            $account->user, $type, $title, $message, 'loyalty',
            route('account.loyalty.index'), 'high',
            ['loyalty_account_id' => $account->id, 'old_tier_id' => $oldTier?->id, 'new_tier_id' => $newTier->id],
            false
        ));
    }
}
