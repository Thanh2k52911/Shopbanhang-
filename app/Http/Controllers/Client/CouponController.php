<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\CouponService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;
use Throwable;

class CouponController extends Controller
{
    public function apply(
        Request $request,
        CouponService $couponService
    ): JsonResponse {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
            ],
        ], [
            'code.required' =>
                'Vui lòng nhập mã giảm giá.',

            'code.max' =>
                'Mã giảm giá không được vượt quá 50 ký tự.',
        ]);

        try {
            $coupon = $couponService->apply(
                $request,
                $validated['code'],
                'manual'
            );

            return response()->json([
                'success' => true,
                'message' =>
                    $coupon['free_shipping']
                        ? 'Áp dụng mã miễn phí vận chuyển thành công.'
                        : 'Áp dụng mã giảm giá thành công.',
                'coupon' => $coupon,
            ]);
        } catch (RuntimeException $exception) {
            return response()->json([
                'success' => false,
                'message' =>
                    $exception->getMessage(),
            ], 422);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'success' => false,
                'message' =>
                    'Không thể áp dụng mã giảm giá lúc này.',
            ], 500);
        }
    }

    public function remove(
        Request $request,
        CouponService $couponService
    ): JsonResponse {
        /*
         * Khách chủ động gỡ mã:
         * không tự áp voucher khác lại trong phiên Checkout hiện tại.
         */
        $couponService->remove(
            $request,
            true
        );

        return response()->json([
            'success' => true,
            'message' =>
                'Đã gỡ mã. Hệ thống sẽ không tự chọn voucher khác.',
        ]);
    }
}
