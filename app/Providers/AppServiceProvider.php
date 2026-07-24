<?php

namespace App\Providers;

use App\Listeners\AuthHistorySubscriber;
use App\Models\ProductFavorite;
use App\Models\Notification;
use App\Models\Order;
use App\Models\ReturnRequest;
use App\Models\Refund;
use App\Models\LoyaltyAccount;
use App\Models\SavedCoupon;
use App\Observers\OrderObserver;
use App\Observers\ReturnRequestObserver;
use App\Observers\RefundObserver;
use App\Observers\LoyaltyAccountObserver;
use App\Observers\SavedCouponObserver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $helperFile = app_path(
            'helpers.php'
        );

        if (file_exists($helperFile)) {
            require_once $helperFile;
        }
    }

    public function boot(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Theo dõi đăng nhập, đăng xuất
        |--------------------------------------------------------------------------
        */

        Event::subscribe(
            AuthHistorySubscriber::class
        );

        /* Theo dõi thay đổi để tạo thông báo phía khách hàng. */
        Order::observe(OrderObserver::class);
        ReturnRequest::observe(ReturnRequestObserver::class);
        Refund::observe(RefundObserver::class);
        LoyaltyAccount::observe(LoyaltyAccountObserver::class);
        SavedCoupon::observe(SavedCouponObserver::class);

        /*
        |--------------------------------------------------------------------------
        | Danh mục thanh điều hướng
        |--------------------------------------------------------------------------
        */

        View::composer(
            'client.partials.navbar',

            function ($view): void {
                $navbarCategories =
                    DB::table('categories')
                        ->where(
                            'status',
                            true
                        )
                        ->whereNull(
                            'deleted_at'
                        )
                        ->orderBy(
                            'sort_order'
                        )
                        ->orderBy('name')
                        ->limit(12)
                        ->get([
                            'id',
                            'name',
                            'slug',
                        ]);

                $view->with(
                    'navbarCategories',
                    $navbarCategories
                );
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Dữ liệu footer
        |--------------------------------------------------------------------------
        */

        View::composer(
            'client.partials.footer',

            function ($view): void {
                $settings =
                    DB::table('settings')
                        ->where(
                            'is_public',
                            true
                        )
                        ->whereIn(
                            'key',
                            [
                                'site_name',
                                'site_description',
                                'hotline',
                                'contact_email',
                                'contact_address',
                                'facebook_url',
                                'instagram_url',
                                'youtube_url',
                                'tiktok_url',
                            ]
                        )
                        ->pluck(
                            'value',
                            'key'
                        );

                $footerPages =
                    DB::table('pages')
                        ->where(
                            'status',
                            true
                        )
                        ->where(
                            'show_in_footer',
                            true
                        )
                        ->whereNull(
                            'deleted_at'
                        )
                        ->orderBy(
                            'sort_order'
                        )
                        ->orderBy('title')
                        ->limit(10)
                        ->get([
                            'title',
                            'slug',
                            'page_type',
                        ]);

                $view->with([
                    'footerSettings' =>
                        $settings,

                    'footerPages' =>
                        $footerPages,
                ]);
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Giỏ hàng và yêu thích trên header
        |--------------------------------------------------------------------------
        */

        View::composer(
            'client.partials.header',

            function ($view): void {
                $cartCount = 0;

                $favoritesCount = 0;

                if (auth()->check()) {
                    $cart =
                        DB::table('carts')
                            ->where(
                                'user_id',
                                auth()->id()
                            )
                            ->where(
                                'status',
                                'active'
                            )
                            ->latest('id')
                            ->first();

                    $favoritesCount =
                        ProductFavorite::query()
                            ->where(
                                'user_id',
                                auth()->id()
                            )
                            ->count();
                } else {
                    $cart =
                        DB::table('carts')
                            ->whereNull(
                                'user_id'
                            )
                            ->where(
                                'session_id',
                                session()
                                    ->getId()
                            )
                            ->where(
                                'status',
                                'active'
                            )
                            ->latest('id')
                            ->first();
                }

                if ($cart) {
                    $cartCount = (int)
                        DB::table('cart_items')
                            ->where(
                                'cart_id',
                                $cart->id
                            )
                            ->sum('quantity');
                }

                $headerNotifications = collect();
                $headerUnreadNotificationCount = 0;

                if (auth()->check()) {
                    $headerNotifications = Notification::query()
                        ->where('notifiable_type', auth()->user()->getMorphClass())
                        ->where('notifiable_id', auth()->id())
                        ->latest('created_at')
                        ->limit(6)
                        ->get();

                    $headerUnreadNotificationCount = Notification::query()
                        ->where('notifiable_type', auth()->user()->getMorphClass())
                        ->where('notifiable_id', auth()->id())
                        ->whereNull('read_at')
                        ->count();
                }

                $view->with([
                    'cartCount' => $cartCount,
                    'favoritesCount' => $favoritesCount,
                    'headerNotifications' => $headerNotifications,
                    'headerUnreadNotificationCount' => $headerUnreadNotificationCount,
                ]);
            }
        );
    }
}
