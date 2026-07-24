<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class LoyaltyController extends Controller
{
    /**
     * Danh sách tài khoản Loyalty.
     */
    public function index(Request $request): View
    {
        $query = DB::table('loyalty_accounts as la')
            ->join('users as u', 'la.user_id', '=', 'u.id')
            ->leftJoin('loyalty_tiers as lt', function ($join): void {
                $join
                    ->on('la.tier_id', '=', 'lt.id')
                    ->whereNull('lt.deleted_at');
            })
            ->whereNull('u.deleted_at')
            ->select([
                'la.id',
                'la.user_id',
                'la.tier_id',
                'la.available_points',
                'la.pending_points',
                'la.lifetime_earned_points',
                'la.lifetime_redeemed_points',
                'la.lifetime_spending',
                'la.tier_started_at',
                'la.tier_expires_at',
                'la.created_at',
                'la.updated_at',

                'u.name as customer_name',
                'u.email as customer_email',
                'u.avatar as customer_avatar',
                'u.status as customer_status',

                'lt.name as tier_name',
                'lt.code as tier_code',
                'lt.color as tier_color',
                'lt.icon as tier_icon',
                'lt.status as tier_status',
            ])
            ->selectSub(
                function (Builder $builder): void {
                    $builder
                        ->from('loyalty_transactions')
                        ->selectRaw('COUNT(*)')
                        ->whereColumn(
                            'loyalty_transactions.loyalty_account_id',
                            'la.id'
                        );
                },
                'transactions_count'
            )
            ->selectSub(
                function (Builder $builder): void {
                    $builder
                        ->from('loyalty_transactions')
                        ->select('created_at')
                        ->whereColumn(
                            'loyalty_transactions.loyalty_account_id',
                            'la.id'
                        )
                        ->latest('created_at')
                        ->limit(1);
                },
                'last_transaction_at'
            );

        $keyword = trim((string) $request->input('keyword'));

        if ($keyword !== '') {
            $query->where(
                function (Builder $builder) use ($keyword): void {
                    $builder
                        ->where(
                            'u.name',
                            'like',
                            '%' . $keyword . '%'
                        )
                        ->orWhere(
                            'u.email',
                            'like',
                            '%' . $keyword . '%'
                        )
                        ->orWhere(
                            'lt.name',
                            'like',
                            '%' . $keyword . '%'
                        )
                        ->orWhere(
                            'lt.code',
                            'like',
                            '%' . $keyword . '%'
                        );
                }
            );
        }

        if ($request->filled('tier_id')) {
            $query->where(
                'la.tier_id',
                (int) $request->input('tier_id')
            );
        }

        match ($request->input('points')) {
            'positive' => $query->where('la.available_points', '>', 0),
            'zero' => $query->where('la.available_points', 0),
            'high' => $query->where('la.available_points', '>=', 1000),
            default => null,
        };

        match ($request->input('tier_expiry')) {
            'expired' => $query
                ->whereNotNull('la.tier_expires_at')
                ->where('la.tier_expires_at', '<', now()),

            'expiring_soon' => $query
                ->whereNotNull('la.tier_expires_at')
                ->whereBetween(
                    'la.tier_expires_at',
                    [
                        now(),
                        now()->addDays(30),
                    ]
                ),

            'valid' => $query->where(
                function (Builder $builder): void {
                    $builder
                        ->whereNull('la.tier_expires_at')
                        ->orWhere('la.tier_expires_at', '>=', now());
                }
            ),

            default => null,
        };

        match ($request->input('customer_status')) {
            'active',
            'inactive',
            'blocked' => $query->where(
                'u.status',
                $request->input('customer_status')
            ),

            default => null,
        };

        match ($request->input('sort')) {
            'oldest' => $query->orderBy('la.id'),
            'name_asc' => $query->orderBy('u.name'),
            'name_desc' => $query->orderByDesc('u.name'),
            'points_desc' => $query->orderByDesc('la.available_points'),
            'points_asc' => $query->orderBy('la.available_points'),
            'spending_desc' => $query->orderByDesc('la.lifetime_spending'),
            'transactions_desc' => $query->orderByDesc('transactions_count'),
            'last_transaction_desc' => $query->orderByDesc(
                'last_transaction_at'
            ),
            default => $query->orderByDesc('la.id'),
        };

        $accounts = $query
            ->paginate(20)
            ->withQueryString();

        $tiers = DB::table('loyalty_tiers')
            ->whereNull('deleted_at')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get([
                'id',
                'name',
                'code',
                'color',
                'status',
            ]);

        $statistics = [
            'total_accounts' => DB::table('loyalty_accounts')
                ->count(),

            'total_available_points' => (int) DB::table(
                'loyalty_accounts'
            )->sum('available_points'),

            'total_pending_points' => (int) DB::table(
                'loyalty_accounts'
            )->sum('pending_points'),

            'lifetime_earned_points' => (int) DB::table(
                'loyalty_accounts'
            )->sum('lifetime_earned_points'),

            'lifetime_redeemed_points' => (int) DB::table(
                'loyalty_accounts'
            )->sum('lifetime_redeemed_points'),

            'lifetime_spending' => (float) DB::table(
                'loyalty_accounts'
            )->sum('lifetime_spending'),

            'expired_tiers' => DB::table('loyalty_accounts')
                ->whereNotNull('tier_expires_at')
                ->where('tier_expires_at', '<', now())
                ->count(),

            'expiring_soon' => DB::table('loyalty_accounts')
                ->whereNotNull('tier_expires_at')
                ->whereBetween(
                    'tier_expires_at',
                    [
                        now(),
                        now()->addDays(30),
                    ]
                )
                ->count(),
        ];

        return view('admin.loyalty.index', [
            'accounts' => $accounts,
            'tiers' => $tiers,
            'statistics' => $statistics,
        ]);
    }

    /**
     * Chi tiết tài khoản Loyalty.
     */
    public function show(
        Request $request,
        int $account
    ): View {
        $loyaltyAccount = DB::table('loyalty_accounts as la')
            ->join('users as u', 'la.user_id', '=', 'u.id')
            ->leftJoin('loyalty_tiers as lt', function ($join): void {
                $join
                    ->on('la.tier_id', '=', 'lt.id')
                    ->whereNull('lt.deleted_at');
            })
            ->where('la.id', $account)
            ->whereNull('u.deleted_at')
            ->select([
                'la.*',

                'u.name as customer_name',
                'u.email as customer_email',
                'u.avatar as customer_avatar',
                'u.status as customer_status',
                'u.last_login_at',
                'u.last_login_ip',

                'lt.name as tier_name',
                'lt.code as tier_code',
                'lt.description as tier_description',
                'lt.minimum_spending as tier_minimum_spending',
                'lt.minimum_points as tier_minimum_points',
                'lt.point_multiplier as tier_point_multiplier',
                'lt.discount_percent as tier_discount_percent',
                'lt.color as tier_color',
                'lt.icon as tier_icon',
                'lt.status as tier_status',
            ])
            ->first();

        abort_if(! $loyaltyAccount, 404);

        $transactionQuery = DB::table(
            'loyalty_transactions as ltx'
        )
            ->leftJoin(
                'orders as o',
                'ltx.order_id',
                '=',
                'o.id'
            )
            ->leftJoin(
                'users as creator',
                'ltx.created_by',
                '=',
                'creator.id'
            )
            ->where(
                'ltx.loyalty_account_id',
                $loyaltyAccount->id
            )
            ->select([
                'ltx.id',
                'ltx.order_id',
                'ltx.type',
                'ltx.points',
                'ltx.balance_before',
                'ltx.balance_after',
                'ltx.monetary_value',
                'ltx.status',
                'ltx.reference_type',
                'ltx.reference_id',
                'ltx.description',
                'ltx.available_at',
                'ltx.expires_at',
                'ltx.created_by',
                'ltx.created_at',
                'ltx.updated_at',

                'o.order_code',
                'creator.name as creator_name',
                'creator.email as creator_email',
            ]);

        if ($request->filled('transaction_type')) {
            $transactionQuery->where(
                'ltx.type',
                $request->input('transaction_type')
            );
        }

        if ($request->filled('transaction_status')) {
            $transactionQuery->where(
                'ltx.status',
                $request->input('transaction_status')
            );
        }

        $transactions = $transactionQuery
            ->orderByDesc('ltx.created_at')
            ->paginate(20)
            ->withQueryString();

        $transactionStatistics = DB::table(
            'loyalty_transactions'
        )
            ->where(
                'loyalty_account_id',
                $loyaltyAccount->id
            )
            ->selectRaw(
                "
                COUNT(*) as total_transactions,
                COALESCE(
                    SUM(
                        CASE
                            WHEN status = 'completed'
                                AND points > 0
                            THEN points
                            ELSE 0
                        END
                    ),
                    0
                ) as credited_points,
                COALESCE(
                    SUM(
                        CASE
                            WHEN status = 'completed'
                                AND points < 0
                            THEN ABS(points)
                            ELSE 0
                        END
                    ),
                    0
                ) as debited_points,
                COALESCE(
                    SUM(
                        CASE
                            WHEN status = 'pending'
                            THEN points
                            ELSE 0
                        END
                    ),
                    0
                ) as pending_transaction_points
                "
            )
            ->first();

        $tiers = DB::table('loyalty_tiers')
            ->whereNull('deleted_at')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get([
                'id',
                'name',
                'code',
                'status',
                'color',
                'minimum_spending',
                'minimum_points',
            ]);

        return view('admin.loyalty.show', [
            'account' => $loyaltyAccount,
            'transactions' => $transactions,
            'transactionStatistics' => $transactionStatistics,
            'tiers' => $tiers,
            'transactionTypes' => $this->transactionTypes(),
            'transactionStatuses' => $this->transactionStatuses(),
        ]);
    }

    /**
     * Admin cộng hoặc trừ điểm thủ công.
     */
    public function adjustPoints(
        Request $request,
        int $account
    ): RedirectResponse {
        $validated = $request->validate(
            [
                'operation' => [
                    'required',
                    Rule::in([
                        'add',
                        'subtract',
                    ]),
                ],

                'points' => [
                    'required',
                    'integer',
                    'min:1',
                    'max:999999999',
                ],

                'monetary_value' => [
                    'nullable',
                    'numeric',
                    'min:0',
                    'max:9999999999999.99',
                ],

                'description' => [
                    'required',
                    'string',
                    'max:2000',
                ],
            ],
            [
                'operation.required' =>
                    'Vui lòng chọn cộng hoặc trừ điểm.',

                'operation.in' =>
                    'Thao tác điều chỉnh điểm không hợp lệ.',

                'points.required' =>
                    'Vui lòng nhập số điểm.',

                'points.integer' =>
                    'Số điểm phải là số nguyên.',

                'points.min' =>
                    'Số điểm phải lớn hơn 0.',

                'description.required' =>
                    'Vui lòng nhập lý do điều chỉnh.',
            ]
        );

        DB::beginTransaction();

        try {
            $loyaltyAccount = DB::table('loyalty_accounts')
                ->where('id', $account)
                ->lockForUpdate()
                ->first();

            abort_if(! $loyaltyAccount, 404);

            $points = (int) $validated['points'];

            $signedPoints = $validated['operation'] === 'add'
                ? $points
                : -$points;

            $balanceBefore = (int) $loyaltyAccount
                ->available_points;

            $balanceAfter = $balanceBefore + $signedPoints;

            if ($balanceAfter < 0) {
                throw ValidationException::withMessages([
                    'points' =>
                        'Không thể trừ vượt quá số điểm khả dụng hiện tại.',
                ]);
            }

            $accountUpdate = [
                'available_points' => $balanceAfter,
                'updated_at' => now(),
            ];

            if ($signedPoints > 0) {
                $accountUpdate['lifetime_earned_points'] =
                    (int) $loyaltyAccount->lifetime_earned_points
                    + $points;
            } else {
                $accountUpdate['lifetime_redeemed_points'] =
                    (int) $loyaltyAccount->lifetime_redeemed_points
                    + $points;
            }

            DB::table('loyalty_accounts')
                ->where('id', $loyaltyAccount->id)
                ->update($accountUpdate);

            DB::table('loyalty_transactions')->insert([
                'loyalty_account_id' => $loyaltyAccount->id,
                'order_id' => null,
                'type' => 'adjust',
                'points' => $signedPoints,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'monetary_value' => (float) (
                    $validated['monetary_value'] ?? 0
                ),
                'status' => 'completed',
                'reference_type' => 'admin_adjustment',
                'reference_id' => auth()->id(),
                'description' => trim(
                    $validated['description']
                ),
                'available_at' => now(),
                'expires_at' => null,
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return back()->with(
                'success',
                $signedPoints > 0
                    ? 'Cộng điểm thành công.'
                    : 'Trừ điểm thành công.'
            );
        } catch (ValidationException $exception) {
            DB::rollBack();

            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();

            report($exception);

            return back()
                ->withInput()
                ->with(
                    'error',
                    app()->isLocal()
                        ? 'Lỗi điều chỉnh điểm: '
                            . $exception->getMessage()
                        : 'Không thể điều chỉnh điểm.'
                );
        }
    }

    /**
     * Đổi hạng thành viên cho tài khoản.
     */
    public function updateTier(
        Request $request,
        int $account
    ): RedirectResponse {
        $validated = $request->validate(
            [
                'tier_id' => [
                    'nullable',
                    'integer',
                    Rule::exists('loyalty_tiers', 'id')
                        ->whereNull('deleted_at'),
                ],

                'tier_started_at' => [
                    'nullable',
                    'date',
                ],

                'tier_expires_at' => [
                    'nullable',
                    'date',
                ],

                'reason' => [
                    'required',
                    'string',
                    'max:2000',
                ],
            ],
            [
                'tier_id.exists' =>
                    'Hạng thành viên không tồn tại.',

                'reason.required' =>
                    'Vui lòng nhập lý do đổi hạng.',
            ]
        );

        if (
            ! empty($validated['tier_started_at'])
            && ! empty($validated['tier_expires_at'])
            && strtotime($validated['tier_expires_at'])
                <= strtotime($validated['tier_started_at'])
        ) {
            throw ValidationException::withMessages([
                'tier_expires_at' =>
                    'Thời gian hết hạn phải sau thời gian bắt đầu.',
            ]);
        }

        DB::beginTransaction();

        try {
            $loyaltyAccount = DB::table('loyalty_accounts')
                ->where('id', $account)
                ->lockForUpdate()
                ->first();

            abort_if(! $loyaltyAccount, 404);

            $newTierId = isset($validated['tier_id'])
                && $validated['tier_id'] !== ''
                    ? (int) $validated['tier_id']
                    : null;

            $oldTierId = $loyaltyAccount->tier_id !== null
                ? (int) $loyaltyAccount->tier_id
                : null;

            if ($newTierId === $oldTierId) {
                throw ValidationException::withMessages([
                    'tier_id' =>
                        'Tài khoản đang thuộc hạng này.',
                ]);
            }

            DB::table('loyalty_accounts')
                ->where('id', $loyaltyAccount->id)
                ->update([
                    'tier_id' => $newTierId,
                    'tier_started_at' => $newTierId !== null
                        ? (
                            $validated['tier_started_at']
                            ?? now()
                        )
                        : null,
                    'tier_expires_at' => $newTierId !== null
                        ? (
                            $validated['tier_expires_at']
                            ?? null
                        )
                        : null,
                    'updated_at' => now(),
                ]);

            DB::table('loyalty_transactions')->insert([
                'loyalty_account_id' => $loyaltyAccount->id,
                'order_id' => null,
                'type' => 'adjust',
                'points' => 0,
                'balance_before' => (int) $loyaltyAccount
                    ->available_points,
                'balance_after' => (int) $loyaltyAccount
                    ->available_points,
                'monetary_value' => 0,
                'status' => 'completed',
                'reference_type' => 'tier_change',
                'reference_id' => $newTierId,
                'description' => trim(
                    $validated['reason']
                )
                    . ' | Tier: '
                    . ($oldTierId ?? 'none')
                    . ' → '
                    . ($newTierId ?? 'none'),
                'available_at' => now(),
                'expires_at' => null,
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return back()->with(
                'success',
                'Cập nhật hạng thành viên thành công.'
            );
        } catch (ValidationException $exception) {
            DB::rollBack();

            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();

            report($exception);

            return back()
                ->withInput()
                ->with(
                    'error',
                    app()->isLocal()
                        ? 'Lỗi cập nhật hạng: '
                            . $exception->getMessage()
                        : 'Không thể cập nhật hạng thành viên.'
                );
        }
    }

    /**
     * Danh sách và cấu hình các hạng Loyalty.
     */
    public function tiers(): View
    {
        $tiers = DB::table('loyalty_tiers as lt')
            ->whereNull('lt.deleted_at')
            ->select([
                'lt.id',
                'lt.name',
                'lt.code',
                'lt.description',
                'lt.minimum_spending',
                'lt.minimum_points',
                'lt.point_multiplier',
                'lt.discount_percent',
                'lt.reward_enabled',
                'lt.reward_name',
                'lt.reward_description',
                'lt.reward_discount_type',
                'lt.reward_discount_value',
                'lt.reward_maximum_discount',
                'lt.reward_minimum_order_amount',
                'lt.reward_valid_days',
                'lt.color',
                'lt.icon',
                'lt.sort_order',
                'lt.status',
                'lt.created_at',
                'lt.updated_at',
            ])
            ->selectSub(
                function (Builder $builder): void {
                    $builder
                        ->from('loyalty_accounts')
                        ->selectRaw('COUNT(*)')
                        ->whereColumn(
                            'loyalty_accounts.tier_id',
                            'lt.id'
                        );
                },
                'accounts_count'
            )
            ->orderBy('lt.sort_order')
            ->orderBy('lt.id')
            ->get();

        $statistics = [
            'total' => $tiers->count(),
            'active' => $tiers
                ->where('status', 1)
                ->count(),
            'inactive' => $tiers
                ->where('status', 0)
                ->count(),
            'assigned_accounts' => (int) $tiers
                ->sum('accounts_count'),
        ];

        return view('admin.loyalty.tiers', [
            'tiers' => $tiers,
            'statistics' => $statistics,
        ]);
    }

    /**
     * Cập nhật cấu hình một hạng Loyalty.
     */
    public function updateTierSetting(
        Request $request,
        int $tier
    ): RedirectResponse {
        $tierDetail = DB::table('loyalty_tiers')
            ->where('id', $tier)
            ->whereNull('deleted_at')
            ->first();

        abort_if(! $tierDetail, 404);

        $validated = $request->validate(
            [
                'name' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'code' => [
                    'required',
                    'string',
                    'max:50',
                    Rule::unique(
                        'loyalty_tiers',
                        'code'
                    )->ignore($tierDetail->id),
                ],

                'description' => [
                    'nullable',
                    'string',
                    'max:5000',
                ],

                'minimum_spending' => [
                    'required',
                    'numeric',
                    'min:0',
                    'max:9999999999999.99',
                ],

                'minimum_points' => [
                    'required',
                    'integer',
                    'min:0',
                ],

                'point_multiplier' => [
                    'required',
                    'numeric',
                    'min:0',
                    'max:999.99',
                ],

                'discount_percent' => [
                    'required',
                    'numeric',
                    'min:0',
                    'max:100',
                ],

                'reward_enabled' => [
                    'nullable',
                    'boolean',
                ],

                'reward_name' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'reward_description' => [
                    'nullable',
                    'string',
                    'max:5000',
                ],

                'reward_discount_type' => [
                    'nullable',
                    Rule::in(['percentage', 'fixed', 'free_shipping']),
                ],

                'reward_discount_value' => [
                    'nullable',
                    'numeric',
                    'min:0',
                    'max:9999999999999.99',
                ],

                'reward_maximum_discount' => [
                    'nullable',
                    'numeric',
                    'min:0',
                    'max:9999999999999.99',
                ],

                'reward_minimum_order_amount' => [
                    'nullable',
                    'numeric',
                    'min:0',
                    'max:9999999999999.99',
                ],

                'reward_valid_days' => [
                    'nullable',
                    'integer',
                    'min:1',
                    'max:3650',
                ],

                'color' => [
                    'nullable',
                    'string',
                    'max:30',
                ],

                'icon' => [
                    'nullable',
                    'string',
                    'max:500',
                ],

                'sort_order' => [
                    'required',
                    'integer',
                    'min:0',
                ],

                'status' => [
                    'nullable',
                    'boolean',
                ],
            ],
            [
                'name.required' =>
                    'Vui lòng nhập tên hạng.',

                'code.required' =>
                    'Vui lòng nhập mã hạng.',

                'code.unique' =>
                    'Mã hạng đã tồn tại.',

                'minimum_spending.required' =>
                    'Vui lòng nhập mức chi tiêu tối thiểu.',

                'minimum_points.required' =>
                    'Vui lòng nhập số điểm tối thiểu.',

                'point_multiplier.required' =>
                    'Vui lòng nhập hệ số nhân điểm.',

                'discount_percent.max' =>
                    'Phần trăm giảm không được vượt quá 100.',
            ]
        );

        if ((bool) ($validated['reward_enabled'] ?? false)) {
            if (empty($validated['reward_discount_type'])) {
                throw ValidationException::withMessages([
                    'reward_discount_type' =>
                        'Vui lòng chọn loại voucher thăng hạng.',
                ]);
            }

            if (
                $validated['reward_discount_type'] !== 'free_shipping'
                && (float) ($validated['reward_discount_value'] ?? 0) <= 0
            ) {
                throw ValidationException::withMessages([
                    'reward_discount_value' =>
                        'Giá trị voucher phải lớn hơn 0.',
                ]);
            }
        }

        try {
            DB::table('loyalty_tiers')
                ->where('id', $tierDetail->id)
                ->update([
                    'name' => trim($validated['name']),
                    'code' => strtolower(
                        trim($validated['code'])
                    ),
                    'description' => $this->nullableTrim(
                        $validated['description'] ?? null
                    ),
                    'minimum_spending' => (float) $validated[
                        'minimum_spending'
                    ],
                    'minimum_points' => (int) $validated[
                        'minimum_points'
                    ],
                    'point_multiplier' => (float) $validated[
                        'point_multiplier'
                    ],
                    'discount_percent' => (float) $validated[
                        'discount_percent'
                    ],
                    'reward_enabled' => (int) (
                        $validated['reward_enabled'] ?? 0
                    ),
                    'reward_name' => $this->nullableTrim(
                        $validated['reward_name'] ?? null
                    ),
                    'reward_description' => $this->nullableTrim(
                        $validated['reward_description'] ?? null
                    ),
                    'reward_discount_type' => $this->nullableTrim(
                        $validated['reward_discount_type'] ?? null
                    ),
                    'reward_discount_value' => isset(
                        $validated['reward_discount_value']
                    ) && $validated['reward_discount_value'] !== ''
                        ? (float) $validated['reward_discount_value']
                        : null,
                    'reward_maximum_discount' => isset(
                        $validated['reward_maximum_discount']
                    ) && $validated['reward_maximum_discount'] !== ''
                        ? (float) $validated['reward_maximum_discount']
                        : null,
                    'reward_minimum_order_amount' => (float) (
                        $validated['reward_minimum_order_amount'] ?? 0
                    ),
                    'reward_valid_days' => (int) (
                        $validated['reward_valid_days'] ?? 30
                    ),
                    'color' => $this->nullableTrim(
                        $validated['color'] ?? null
                    ),
                    'icon' => $this->nullableTrim(
                        $validated['icon'] ?? null
                    ),
                    'sort_order' => (int) $validated[
                        'sort_order'
                    ],
                    'status' => (int) (
                        $validated['status'] ?? 0
                    ),
                    'updated_at' => now(),
                ]);

            return back()->with(
                'success',
                'Cập nhật hạng Loyalty thành công.'
            );
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with(
                    'error',
                    app()->isLocal()
                        ? 'Lỗi cập nhật hạng: '
                            . $exception->getMessage()
                        : 'Không thể cập nhật hạng Loyalty.'
                );
        }
    }

    private function transactionTypes(): array
    {
        return [
            'earn' => 'Cộng điểm',
            'redeem' => 'Đổi điểm',
            'refund' => 'Hoàn điểm',
            'expire' => 'Điểm hết hạn',
            'adjust' => 'Admin điều chỉnh',
            'cancel' => 'Hủy điểm',
        ];
    }

    private function transactionStatuses(): array
    {
        return [
            'pending' => 'Chờ xác nhận',
            'completed' => 'Đã áp dụng',
            'cancelled' => 'Đã hủy',
            'expired' => 'Đã hết hạn',
        ];
    }

    private function nullableTrim(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value !== ''
            ? $value
            : null;
    }
}
