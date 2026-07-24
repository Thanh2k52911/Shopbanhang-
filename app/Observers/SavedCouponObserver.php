<?php

namespace App\Observers;

use App\Models\SavedCoupon;
use App\Services\ClientNotificationService;

class SavedCouponObserver
{
    public function __construct(private readonly ClientNotificationService $notifications) {}

    public function created(SavedCoupon $savedCoupon): void
    {
        $savedCoupon->loadMissing(['user', 'coupon']);
        if (! $savedCoupon->user || ! $savedCoupon->coupon) return;

        $this->notifications->safely(fn () => $this->notifications->send(
            $savedCoupon->user,
            'coupon.granted',
            'Bạn vừa nhận được một ưu đãi mới',
            sprintf('Voucher %s đã được thêm vào Ví voucher của bạn.', $savedCoupon->coupon->code),
            'promotion',
            route('account.coupons.index'),
            'normal',
            ['saved_coupon_id' => $savedCoupon->id, 'coupon_id' => $savedCoupon->coupon_id, 'coupon_code' => $savedCoupon->coupon->code]
        ));
    }
}
