<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\SavedCoupon;
use App\Services\CouponService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

class SavedCouponController extends Controller
{
    /**
     * Ví voucher và voucher mới từ Shop.
     */
    public function index(
        Request $request
    ): View {
        $userId = (int) $request
            ->user()
            ->id;

        $savedCoupons = SavedCoupon::query()
            ->with([
                'coupon.products:id,name',
                'coupon.categories:id,name',
            ])
            ->where('user_id', $userId)
            ->whereHas(
                'coupon',
                fn ($query) => $query
                    ->whereNull('deleted_at')
            )
            ->latest('saved_at')
            ->paginate(
                12,
                ['*'],
                'saved_page'
            );

        $savedCouponIds = SavedCoupon::query()
            ->where('user_id', $userId)
            ->pluck('coupon_id');

        $shopCoupons = Coupon::query()
            ->with([
                'products:id,name',
                'categories:id,name',
            ])
            ->whereNull('deleted_at')
            ->where('status', true)
            ->where(function ($dateQuery): void {
                $dateQuery
                    ->whereNull('start_at')
                    ->orWhere(
                        'start_at',
                        '<=',
                        now()
                    );
            })
            ->where(function ($dateQuery): void {
                $dateQuery
                    ->whereNull('end_at')
                    ->orWhere(
                        'end_at',
                        '>=',
                        now()
                    );
            })
            ->where(function ($accessQuery) use (
                $userId
            ): void {
                $accessQuery
                    ->where('is_public', true)
                    ->orWhereHas(
                        'users',
                        fn ($userQuery) =>
                            $userQuery->where(
                                'users.id',
                                $userId
                            )
                    );
            })
            ->whereNotIn(
                'id',
                $savedCouponIds
            )
            ->orderByDesc('created_at')
            ->limit(24)
            ->get();

        return view(
            'client.account.coupons.index',
            compact(
                'savedCoupons',
                'shopCoupons'
            )
        );
    }

    /**
     * Tất cả voucher phù hợp cho modal Checkout.
     */
    public function checkoutList(
        Request $request,
        CouponService $couponService
    ): JsonResponse {
        $shippingMethod = DB::table(
            'shipping_methods'
        )
            ->where('status', true)
            ->whereNull('deleted_at')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->first([
                'base_fee',
            ]);

        $shippingFee = (float) (
            $shippingMethod?->base_fee
            ?? 0
        );

        $coupons =
            $couponService->availableCoupons(
                $request,
                $shippingFee
            );

        return response()->json([
            'success' => true,
            'coupons' => $coupons,
            'best_coupon_code' =>
                $coupons->firstWhere(
                    'is_best',
                    true
                )['code'] ?? null,
        ]);
    }

    public function store(
        Request $request,
        Coupon $coupon
    ): JsonResponse {
        if (
            ! $coupon->status
            || $coupon->deleted_at !== null
        ) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Mã giảm giá này hiện không khả dụng.',
            ], 422);
        }

        if (! $coupon->is_public) {
            $allowed = DB::table(
                'coupon_users'
            )
                ->where(
                    'coupon_id',
                    $coupon->id
                )
                ->where(
                    'user_id',
                    $request->user()->id
                )
                ->exists();

            if (! $allowed) {
                return response()->json([
                    'success' => false,
                    'message' =>
                        'Mã này không dành cho tài khoản của bạn.',
                ], 403);
            }
        }

        try {
            $savedCoupon =
                SavedCoupon::query()
                    ->firstOrCreate(
                        [
                            'user_id' =>
                                $request
                                    ->user()
                                    ->id,

                            'coupon_id' =>
                                $coupon->id,
                        ],
                        [
                            'saved_at' =>
                                now(),
                        ]
                    );

            return response()->json([
                'success' => true,
                'already_saved' =>
                    ! $savedCoupon
                        ->wasRecentlyCreated,

                'message' =>
                    $savedCoupon
                        ->wasRecentlyCreated
                        ? 'Đã lưu voucher vào Ví của bạn.'
                        : 'Voucher này đã có trong Ví.',
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'success' => false,
                'message' =>
                    'Không thể lưu voucher lúc này.',
            ], 500);
        }
    }

    public function storeByCode(
        Request $request
    ): JsonResponse {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
            ],
        ]);

        $coupon = Coupon::query()
            ->whereRaw(
                'UPPER(code) = ?',
                [
                    mb_strtoupper(
                        trim(
                            $validated['code']
                        )
                    ),
                ]
            )
            ->first();

        if (! $coupon) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Mã giảm giá không tồn tại.',
            ], 404);
        }

        return $this->store(
            $request,
            $coupon
        );
    }

    public function destroy(
        Request $request,
        SavedCoupon $savedCoupon
    ): JsonResponse {
        if (
            (int) $savedCoupon->user_id
            !== (int) $request
                ->user()
                ->id
        ) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Bạn không có quyền xóa mã này.',
            ], 403);
        }

        $savedCoupon->delete();

        return response()->json([
            'success' => true,
            'message' =>
                'Đã xóa voucher khỏi Ví.',
        ]);
    }
}
