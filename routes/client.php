<?php

use App\Http\Controllers\Client\AccountController;
use App\Http\Controllers\Client\CartController;
use App\Http\Controllers\Client\BuyNowController;
use App\Http\Controllers\Client\CheckoutController;
use App\Http\Controllers\Client\CouponController;
use App\Http\Controllers\Client\HomeController;
use App\Http\Controllers\Client\LoyaltyController;
use App\Http\Controllers\Client\NewsletterController;
use App\Http\Controllers\Client\NotificationController;
use App\Http\Controllers\Client\OrderController;
use App\Http\Controllers\Client\PageController;
use App\Http\Controllers\Client\PasswordOtpController;
use App\Http\Controllers\Client\ProductController;
use App\Http\Controllers\Client\ProductFavoriteController;
use App\Http\Controllers\Client\ProductQuestionController;
use App\Http\Controllers\Client\ProductReviewController;
use App\Http\Controllers\Client\RecentlyViewedProductController;
use App\Http\Controllers\Client\ReturnRequestController;
use App\Http\Controllers\Client\SavedCouponController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Client\ContactController;
use App\Http\Controllers\Client\UserAddressController;
use App\Http\Controllers\Client\SupportChatController;
use App\Http\Controllers\Client\ReviewReplyController;

/*
|--------------------------------------------------------------------------
| Trang chủ
|--------------------------------------------------------------------------
*/

Route::get(
    '/',
    [HomeController::class, 'index']
)->name('home');

/*
|--------------------------------------------------------------------------
| Sản phẩm
|--------------------------------------------------------------------------
*/

Route::get(
    '/products',
    [ProductController::class, 'index']
)->name('products.index');

Route::get(
    '/products/{slug}',
    [ProductController::class, 'show']
)->name('products.show');

/*
|--------------------------------------------------------------------------
| Hỏi đáp sản phẩm
|--------------------------------------------------------------------------
*/

Route::post(
    '/products/{product}/questions',
    [ProductQuestionController::class, 'store']
)
    ->middleware('throttle:5,1')
    ->name('products.questions.store');
Route::post(
    '/product-questions/{question}/replies',
    [ProductQuestionController::class, 'reply']
)
    ->middleware([
        'auth',
        'user.active',
        'verified',
        'throttle:30,1',
    ])
    ->whereNumber('question')
    ->name('products.questions.reply');
/*
|--------------------------------------------------------------------------
| Newsletter
|--------------------------------------------------------------------------
*/

Route::post(
    '/newsletter',
    [NewsletterController::class, 'store']
)
    ->middleware('throttle:3,1')
    ->name('newsletter.store');

/*
|--------------------------------------------------------------------------
| Trang nội dung
|--------------------------------------------------------------------------
*/

Route::get(
    '/trang/{slug}',
    [PageController::class, 'show']
)->name('pages.show');

/*
|--------------------------------------------------------------------------
| Liên hệ
|--------------------------------------------------------------------------
*/

Route::get(
    '/lien-he',
    [ContactController::class, 'create']
)->name('contact.create');

Route::post(
    '/lien-he',
    [ContactController::class, 'store']
)->middleware('throttle:5,1')
  ->name('contact.store');
/*
|--------------------------------------------------------------------------
| Giỏ hàng
|--------------------------------------------------------------------------
*/

Route::get(
    '/cart',
    [CartController::class, 'index']
)->name('cart.index');

Route::post(
    '/cart/items',
    [CartController::class, 'store']
)
    ->middleware('throttle:60,1')
    ->name('cart.items.store');

Route::patch(
    '/cart/items/{itemId}',
    [CartController::class, 'update']
)
    ->middleware('throttle:60,1')
    ->name('cart.items.update');

Route::delete(
    '/cart/items/{itemId}',
    [CartController::class, 'destroy']
)
    ->middleware('throttle:60,1')
    ->name('cart.items.destroy');

Route::post(
    '/buy-now',
    [BuyNowController::class, 'store']
)
    ->middleware('throttle:30,1')
    ->name('buy-now.store');

/*
|--------------------------------------------------------------------------
| Thanh toán
|--------------------------------------------------------------------------
*/

Route::get(
    '/checkout',
    [CheckoutController::class, 'index']
)->name('checkout.index');

Route::post(
    '/checkout',
    [CheckoutController::class, 'store']
)
    ->middleware('throttle:5,1')
    ->name('checkout.store');

Route::get(
    '/checkout/success/{orderCode}',
    [CheckoutController::class, 'success']
)->name('checkout.success');

/*
|--------------------------------------------------------------------------
| Coupon tại Checkout
|--------------------------------------------------------------------------
*/

Route::post(
    '/checkout/coupon',
    [CouponController::class, 'apply']
)
    ->middleware('throttle:20,1')
    ->name('checkout.coupon.apply');

Route::delete(
    '/checkout/coupon',
    [CouponController::class, 'remove']
)
    ->middleware('throttle:20,1')
    ->name('checkout.coupon.remove');

/*
|--------------------------------------------------------------------------
| Ưu đãi đã lưu tại Checkout
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'user.active',
    'verified',
])
    ->prefix('account')
    ->name('account.')
    ->group(function (): void {
        Route::get(
            '/checkout/saved-coupons',
            [SavedCouponController::class, 'checkoutList']
        )->name('checkout.saved-coupons.index');
    });

/*
|--------------------------------------------------------------------------
| Tài khoản khách hàng
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'user.active',
    'verified',
])
    ->prefix('account')
    ->name('account.')
    ->group(function (): void {
        /*
        |--------------------------------------------------------------------------
        | Dashboard tài khoản
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/',
            [AccountController::class, 'index']
        )->name('index');

        /* Thông báo khách hàng */
        Route::get('/notifications', [NotificationController::class, 'index'])
            ->name('notifications.index');
        Route::get('/notifications/{notification}/open', [NotificationController::class, 'open'])
            ->name('notifications.open');
        Route::patch('/notifications/read-all', [NotificationController::class, 'markAllRead'])
            ->name('notifications.read-all');
        Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markRead'])
            ->name('notifications.read');
        Route::patch('/notifications/{notification}/unread', [NotificationController::class, 'markUnread'])
            ->name('notifications.unread');
        Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])
            ->name('notifications.destroy');

        /*
        |--------------------------------------------------------------------------
        | Hồ sơ cá nhân
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/profile',
            [AccountController::class, 'edit']
        )->name('profile.edit');

        Route::patch(
            '/profile',
            [AccountController::class, 'update']
        )->name('profile.update');

        /*
        |--------------------------------------------------------------------------
        | Đổi mật khẩu bằng OTP email
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/password',
            [PasswordOtpController::class, 'requestForm']
        )->name('password.request');

        Route::post(
            '/password/otp',
            [PasswordOtpController::class, 'send']
        )->name('password.otp.send');

        Route::get(
            '/password/otp',
            [PasswordOtpController::class, 'otpForm']
        )->name('password.otp.form');

        Route::post(
            '/password/otp/verify',
            [PasswordOtpController::class, 'verify']
        )->name('password.otp.verify');

        Route::get(
            '/password/change',
            [PasswordOtpController::class, 'changeForm']
        )->name('password.change');

        Route::patch(
            '/password/change',
            [PasswordOtpController::class, 'update']
        )->name('password.update');

        /*
|--------------------------------------------------------------------------
| Sổ địa chỉ
|--------------------------------------------------------------------------
*/

Route::get(
    '/addresses',
    [UserAddressController::class, 'index']
)->name('addresses.index');

Route::get(
    '/addresses/create',
    [UserAddressController::class, 'create']
)->name('addresses.create');

Route::post(
    '/addresses',
    [UserAddressController::class, 'store']
)->name('addresses.store');

Route::get(
    '/addresses/{address}/edit',
    [UserAddressController::class, 'edit']
)
    ->whereNumber('address')
    ->name('addresses.edit');

Route::put(
    '/addresses/{address}',
    [UserAddressController::class, 'update']
)
    ->whereNumber('address')
    ->name('addresses.update');

Route::patch(
    '/addresses/{address}/default',
    [UserAddressController::class, 'setDefault']
)
    ->whereNumber('address')
    ->name('addresses.default');

Route::delete(
    '/addresses/{address}',
    [UserAddressController::class, 'destroy']
)
    ->whereNumber('address')
    ->name('addresses.destroy');

    // Repply//
    Route::post(
    '/reviews/{review}/replies',
    [ReviewReplyController::class, 'store']
)
    ->middleware('throttle:15,1')
    ->name('reviews.replies.store');
        /*
        |--------------------------------------------------------------------------
        | Điểm tích lũy
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/loyalty',
            [LoyaltyController::class, 'index']
        )->name('loyalty.index');

        /*
        |--------------------------------------------------------------------------
        | Ưu đãi của tôi
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/coupons',
            [SavedCouponController::class, 'index']
        )->name('coupons.index');

        Route::post(
            '/coupons/save-by-code',
            [SavedCouponController::class, 'storeByCode']
        )->name('coupons.save-by-code');

        Route::post(
            '/coupons/{coupon}',
            [SavedCouponController::class, 'store']
        )->name('coupons.store');

        Route::delete(
            '/coupons/{savedCoupon}',
            [SavedCouponController::class, 'destroy']
        )->name('coupons.destroy');

        /*
        |--------------------------------------------------------------------------
        | Đơn hàng của tôi
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/orders',
            [OrderController::class, 'index']
        )->name('orders.index');

        Route::get(
            '/orders/{orderCode}',
            [OrderController::class, 'show']
        )->name('orders.show');

        Route::patch(
            '/orders/{orderCode}/cancel',
            [OrderController::class, 'cancel']
        )->name('orders.cancel');

        /*
        |--------------------------------------------------------------------------
        | Yêu cầu trả hàng, đổi hàng và hoàn tiền
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/return-requests',
            [ReturnRequestController::class, 'index']
        )->name('return-requests.index');

        Route::get(
            '/orders/{orderCode}/return-request/create',
            [ReturnRequestController::class, 'create']
        )->name('return-requests.create');

        Route::post(
            '/orders/{orderCode}/return-request',
            [ReturnRequestController::class, 'store']
        )->name('return-requests.store');

        Route::get(
            '/return-requests/{returnCode}',
            [ReturnRequestController::class, 'show']
        )->name('return-requests.show');

        Route::patch(
            '/return-requests/{returnCode}/cancel',
            [ReturnRequestController::class, 'cancel']
        )->name('return-requests.cancel');


        /*
|--------------------------------------------------------------------------
| Chat hỗ trợ trực tiếp
|--------------------------------------------------------------------------
*/

Route::get(
    '/support-chat',
    [SupportChatController::class, 'show']
)->name('support-chat.show');

Route::post(
    '/support-chat',
    [SupportChatController::class, 'store']
)
    ->middleware('throttle:20,1')
    ->name('support-chat.store');

Route::get(
    '/support-chat/messages',
    [SupportChatController::class, 'messages']
)
    ->middleware('throttle:60,1')
    ->name('support-chat.messages');
        /*
        |--------------------------------------------------------------------------
        | Đánh giá sản phẩm
        |--------------------------------------------------------------------------
        */

        Route::post(
            '/reviews',
            [ProductReviewController::class, 'store']
        )->name('reviews.store');

        Route::post(
            '/reviews/{review}/like',
            [ProductReviewController::class, 'toggleLike']
        )->name('reviews.like');

        /*
        |--------------------------------------------------------------------------
        | Sản phẩm yêu thích
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/favorites',
            [ProductFavoriteController::class, 'index']
        )->name('favorites.index');

        Route::post(
            '/favorites/products/{product}/toggle',
            [ProductFavoriteController::class, 'toggle']
        )->name('favorites.toggle');

        Route::delete(
            '/favorites/{favorite}',
            [ProductFavoriteController::class, 'destroy']
        )->name('favorites.destroy');
    });

/*
|--------------------------------------------------------------------------
| Sản phẩm đã xem gần đây
|--------------------------------------------------------------------------
*/

Route::get(
    '/recently-viewed',
    [RecentlyViewedProductController::class, 'index']
)->name('recently-viewed.index');

Route::delete(
    '/recently-viewed/{recentlyViewedProduct}',
    [RecentlyViewedProductController::class, 'destroy']
)
    ->middleware('throttle:30,1')
    ->name('recently-viewed.destroy');

Route::delete(
    '/recently-viewed',
    [RecentlyViewedProductController::class, 'clear']
)
    ->middleware('throttle:10,1')
    ->name('recently-viewed.clear');
