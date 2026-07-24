<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ReturnRequestController;
use App\Http\Controllers\Admin\ShipmentController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\WarehouseController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\DiscountCampaignController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\LoyaltyController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\NewsletterSubscriberController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StatisticsController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\ShippingMethodController;
use App\Http\Controllers\Admin\VariantAttributeController;
use App\Http\Controllers\Admin\VariantValueController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\LoginHistoryController;
use App\Http\Controllers\Admin\SearchHistoryController;
use App\Http\Controllers\Admin\SupportChatController;

Route::middleware([
    'auth',
    'user.active',
    'admin.access',
    'admin.audit',
])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {

        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/',
            [DashboardController::class, 'index']
        )->name('dashboard');

        /*
        |--------------------------------------------------------------------------
        | Quản lý đơn hàng
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/orders',
            [OrderController::class, 'index']
        )->name('orders.index');

        Route::get(
            '/orders/{order}',
            [OrderController::class, 'show']
        )
            ->whereNumber('order')
            ->name('orders.show');

        Route::patch(
            '/orders/{order}/status',
            [OrderController::class, 'updateStatus']
        )
            ->whereNumber('order')
            ->name('orders.update-status');

        Route::patch(
            '/orders/{order}/cancel',
            [OrderController::class, 'cancel']
        )
            ->whereNumber('order')
            ->name('orders.cancel');

            Route::get(
    '/statistics',
    [StatisticsController::class, 'index']
)->name('statistics.index');
        /*
        |--------------------------------------------------------------------------
        | Quản lý vận chuyển
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/shipments',
            [ShipmentController::class, 'index']
        )->name('shipments.index');

        Route::get(
            '/shipments/{shipment}',
            [ShipmentController::class, 'show']
        )
            ->whereNumber('shipment')
            ->name('shipments.show');

        Route::patch(
            '/shipments/{shipment}/status',
            [ShipmentController::class, 'updateStatus']
        )
            ->whereNumber('shipment')
            ->name('shipments.update-status');

        /*
        |--------------------------------------------------------------------------
        | Tạo vận chuyển từ đơn hàng
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/orders/{order}/shipment/create',
            [ShipmentController::class, 'create']
        )
            ->whereNumber('order')
            ->name('shipments.create');

        Route::post(
            '/orders/{order}/shipment',
            [ShipmentController::class, 'store']
        )
            ->whereNumber('order')
            ->name('shipments.store');


        Route::post(
            '/orders/{order}/shipment/automatic',
            [ShipmentController::class, 'storeAutomatic']
        )
            ->whereNumber('order')
            ->name('shipments.store-automatic');

        /*
        |--------------------------------------------------------------------------
        | Quản lý thanh toán
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/payments',
            [PaymentController::class, 'index']
        )->name('payments.index');

        Route::get(
            '/payments/{payment}',
            [PaymentController::class, 'show']
        )
            ->whereNumber('payment')
            ->name('payments.show');

        Route::patch(
            '/payments/{payment}/status',
            [PaymentController::class, 'updateStatus']
        )
            ->whereNumber('payment')
            ->name('payments.update-status');

        Route::post(
            '/payments/{payment}/refunds',
            [PaymentController::class, 'storeRefund']
        )
            ->whereNumber('payment')
            ->name('payments.refunds.store');

        Route::patch(
            '/payments/{payment}/refunds/{refund}/status',
            [PaymentController::class, 'updateRefundStatus']
        )
            ->whereNumber('payment')
            ->whereNumber('refund')
            ->name('payments.refunds.update-status');

        /*
        |--------------------------------------------------------------------------
        | Quản lý yêu cầu trả hàng
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/return-requests',
            [ReturnRequestController::class, 'index']
        )->name('return-requests.index');

        Route::get(
            '/return-requests/{returnRequest}',
            [ReturnRequestController::class, 'show']
        )
            ->whereNumber('returnRequest')
            ->name('return-requests.show');

        Route::patch(
            '/return-requests/{returnRequest}/status',
            [ReturnRequestController::class, 'updateStatus']
        )
            ->whereNumber('returnRequest')
            ->name('return-requests.update-status');

        /*
        |--------------------------------------------------------------------------
        | Quản lý sản phẩm
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/products',
            [ProductController::class, 'index']
        )->name('products.index');

        Route::get(
            '/products/create',
            [ProductController::class, 'create']
        )->name('products.create');

        Route::post(
            '/products',
            [ProductController::class, 'store']
        )->name('products.store');

        Route::get(
            '/products/{product}/edit',
            [ProductController::class, 'edit']
        )
            ->whereNumber('product')
            ->name('products.edit');

        Route::put(
            '/products/{product}',
            [ProductController::class, 'update']
        )
            ->whereNumber('product')
            ->name('products.update');

        Route::delete(
            '/products/{product}',
            [ProductController::class, 'destroy']
        )
            ->whereNumber('product')
            ->name('products.destroy');

        Route::get(
            '/products/{product}',
            [ProductController::class, 'show']
        )
            ->whereNumber('product')
            ->name('products.show');

                /*
|--------------------------------------------------------------------------
| Quản lý nhà cung cấp
|--------------------------------------------------------------------------
*/

Route::get(
    '/suppliers',
    [SupplierController::class, 'index']
)->name('suppliers.index');

Route::get(
    '/suppliers/create',
    [SupplierController::class, 'create']
)->name('suppliers.create');

Route::post(
    '/suppliers',
    [SupplierController::class, 'store']
)->name('suppliers.store');

Route::get(
    '/suppliers/{supplier}/edit',
    [SupplierController::class, 'edit']
)
    ->whereNumber('supplier')
    ->name('suppliers.edit');

Route::put(
    '/suppliers/{supplier}',
    [SupplierController::class, 'update']
)
    ->whereNumber('supplier')
    ->name('suppliers.update');

Route::delete(
    '/suppliers/{supplier}',
    [SupplierController::class, 'destroy']
)
    ->whereNumber('supplier')
    ->name('suppliers.destroy');

Route::get(
    '/suppliers/{supplier}',
    [SupplierController::class, 'show']
)
    ->whereNumber('supplier')
    ->name('suppliers.show');
              /*
        |--------------------------------------------------------------------------
        | Quản lý thương hiệu
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/brands',
            [BrandController::class, 'index']
        )->name('brands.index');

        Route::get(
            '/brands/create',
            [BrandController::class, 'create']
        )->name('brands.create');

        Route::post(
            '/brands',
            [BrandController::class, 'store']
        )->name('brands.store');

        Route::get(
            '/brands/{brand}/edit',
            [BrandController::class, 'edit']
        )
            ->whereNumber('brand')
            ->name('brands.edit');

        Route::put(
            '/brands/{brand}',
            [BrandController::class, 'update']
        )
            ->whereNumber('brand')
            ->name('brands.update');

        Route::delete(
            '/brands/{brand}',
            [BrandController::class, 'destroy']
        )
            ->whereNumber('brand')
            ->name('brands.destroy');

        Route::get(
            '/brands/{brand}',
            [BrandController::class, 'show']
        )
            ->whereNumber('brand')
            ->name('brands.show');

            /*
|--------------------------------------------------------------------------
| Quản lý kho hàng
|--------------------------------------------------------------------------
*/

Route::get(
    '/warehouses',
    [WarehouseController::class, 'index']
)->name('warehouses.index');

Route::get(
    '/warehouses/create',
    [WarehouseController::class, 'create']
)->name('warehouses.create');

Route::post(
    '/warehouses',
    [WarehouseController::class, 'store']
)->name('warehouses.store');

Route::get(
    '/warehouses/{warehouse}/edit',
    [WarehouseController::class, 'edit']
)
    ->whereNumber('warehouse')
    ->name('warehouses.edit');

Route::put(
    '/warehouses/{warehouse}',
    [WarehouseController::class, 'update']
)
    ->whereNumber('warehouse')
    ->name('warehouses.update');

Route::delete(
    '/warehouses/{warehouse}',
    [WarehouseController::class, 'destroy']
)
    ->whereNumber('warehouse')
    ->name('warehouses.destroy');

Route::get(
    '/warehouses/{warehouse}',
    [WarehouseController::class, 'show']
)
    ->whereNumber('warehouse')
    ->name('warehouses.show');

    /*
|--------------------------------------------------------------------------
| Chat hỗ trợ khách hàng
|--------------------------------------------------------------------------
*/

Route::get(
    '/support-chats',
    [SupportChatController::class, 'index']
)->name('support-chats.index');

Route::get(
    '/support-chats/{supportConversation}',
    [SupportChatController::class, 'show']
)
    ->whereNumber('supportConversation')
    ->name('support-chats.show');

Route::post(
    '/support-chats/{supportConversation}/reply',
    [SupportChatController::class, 'reply']
)
    ->whereNumber('supportConversation')
    ->middleware('throttle:30,1')
    ->name('support-chats.reply');

Route::patch(
    '/support-chats/{supportConversation}/assign',
    [SupportChatController::class, 'assign']
)
    ->whereNumber('supportConversation')
    ->name('support-chats.assign');

Route::patch(
    '/support-chats/{supportConversation}/close',
    [SupportChatController::class, 'close']
)
    ->whereNumber('supportConversation')
    ->name('support-chats.close');

Route::patch(
    '/support-chats/{supportConversation}/reopen',
    [SupportChatController::class, 'reopen']
)
    ->whereNumber('supportConversation')
    ->name('support-chats.reopen');

Route::get(
    '/support-chats/{supportConversation}/messages',
    [SupportChatController::class, 'messages']
)
    ->whereNumber('supportConversation')
    ->middleware('throttle:60,1')
    ->name('support-chats.messages');
    /*
|--------------------------------------------------------------------------
| Quản lý tồn kho
|--------------------------------------------------------------------------
*/

Route::get(
    '/inventories',
    [InventoryController::class, 'index']
)->name('inventories.index');

Route::get(
    '/inventories/{inventory}',
    [InventoryController::class, 'show']
)
    ->whereNumber('inventory')
    ->name('inventories.show');

Route::patch(
    '/inventories/{inventory}/minimum-stock',
    [InventoryController::class, 'updateMinimumStock']
)
    ->whereNumber('inventory')
    ->name('inventories.update-minimum-stock');

Route::post(
    '/inventories/{inventory}/transactions',
    [InventoryController::class, 'storeTransaction']
)
    ->whereNumber('inventory')
    ->name('inventories.transactions.store');

/*
|--------------------------------------------------------------------------
| Quản lý mã giảm giá
|--------------------------------------------------------------------------
*/

Route::get(
    '/coupons',
    [CouponController::class, 'index']
)->name('coupons.index');

Route::get(
    '/coupons/create',
    [CouponController::class, 'create']
)->name('coupons.create');

Route::post(
    '/coupons',
    [CouponController::class, 'store']
)->name('coupons.store');

Route::get(
    '/coupons/{coupon}/edit',
    [CouponController::class, 'edit']
)
    ->whereNumber('coupon')
    ->name('coupons.edit');

Route::put(
    '/coupons/{coupon}',
    [CouponController::class, 'update']
)
    ->whereNumber('coupon')
    ->name('coupons.update');

Route::delete(
    '/coupons/{coupon}',
    [CouponController::class, 'destroy']
)
    ->whereNumber('coupon')
    ->name('coupons.destroy');

Route::get(
    '/coupons/{coupon}',
    [CouponController::class, 'show']
)
    ->whereNumber('coupon')
    ->name('coupons.show');

/*
|--------------------------------------------------------------------------
| Quản lý chiến dịch giảm giá
|--------------------------------------------------------------------------
*/

Route::get(
    '/discount-campaigns',
    [DiscountCampaignController::class, 'index']
)->name('discount-campaigns.index');

Route::get(
    '/discount-campaigns/create',
    [DiscountCampaignController::class, 'create']
)->name('discount-campaigns.create');

Route::post(
    '/discount-campaigns',
    [DiscountCampaignController::class, 'store']
)->name('discount-campaigns.store');

Route::get(
    '/discount-campaigns/{discountCampaign}/edit',
    [DiscountCampaignController::class, 'edit']
)
    ->whereNumber('discountCampaign')
    ->name('discount-campaigns.edit');

Route::put(
    '/discount-campaigns/{discountCampaign}',
    [DiscountCampaignController::class, 'update']
)
    ->whereNumber('discountCampaign')
    ->name('discount-campaigns.update');

Route::delete(
    '/discount-campaigns/{discountCampaign}',
    [DiscountCampaignController::class, 'destroy']
)
    ->whereNumber('discountCampaign')
    ->name('discount-campaigns.destroy');

Route::get(
    '/discount-campaigns/{discountCampaign}',
    [DiscountCampaignController::class, 'show']
)
    ->whereNumber('discountCampaign')
    ->name('discount-campaigns.show');

    /*
|--------------------------------------------------------------------------
| Nhân viên quản trị
|--------------------------------------------------------------------------
*/

Route::get(
    '/staff',
    [StaffController::class, 'index']
)->name('staff.index');

Route::get(
    '/staff/create',
    [StaffController::class, 'create']
)->name('staff.create');

Route::post(
    '/staff',
    [StaffController::class, 'store']
)->name('staff.store');

Route::get(
    '/staff/{staff}/edit',
    [StaffController::class, 'edit']
)
    ->whereNumber('staff')
    ->name('staff.edit');

Route::put(
    '/staff/{staff}',
    [StaffController::class, 'update']
)
    ->whereNumber('staff')
    ->name('staff.update');

Route::delete(
    '/staff/{staff}',
    [StaffController::class, 'destroy']
)
    ->whereNumber('staff')
    ->name('staff.destroy');

/*
|--------------------------------------------------------------------------
| Vai trò và phân quyền
|--------------------------------------------------------------------------
*/

Route::get(
    '/roles',
    [RoleController::class, 'index']
)->name('roles.index');

Route::get(
    '/roles/create',
    [RoleController::class, 'create']
)->name('roles.create');

Route::post(
    '/roles',
    [RoleController::class, 'store']
)->name('roles.store');

Route::get(
    '/roles/{role}/edit',
    [RoleController::class, 'edit']
)
    ->whereNumber('role')
    ->name('roles.edit');

Route::put(
    '/roles/{role}',
    [RoleController::class, 'update']
)
    ->whereNumber('role')
    ->name('roles.update');

Route::delete(
    '/roles/{role}',
    [RoleController::class, 'destroy']
)
    ->whereNumber('role')
    ->name('roles.destroy');
/*
|--------------------------------------------------------------------------
| Quản lý banner
|--------------------------------------------------------------------------
*/

Route::get(
    '/banners',
    [BannerController::class, 'index']
)->name('banners.index');

Route::get(
    '/banners/create',
    [BannerController::class, 'create']
)->name('banners.create');

Route::post(
    '/banners',
    [BannerController::class, 'store']
)->name('banners.store');

Route::get(
    '/banners/{banner}/edit',
    [BannerController::class, 'edit']
)
    ->whereNumber('banner')
    ->name('banners.edit');

Route::put(
    '/banners/{banner}',
    [BannerController::class, 'update']
)
    ->whereNumber('banner')
    ->name('banners.update');

Route::delete(
    '/banners/{banner}',
    [BannerController::class, 'destroy']
)
    ->whereNumber('banner')
    ->name('banners.destroy');

Route::get(
    '/banners/{banner}',
    [BannerController::class, 'show']
)
    ->whereNumber('banner')
    ->name('banners.show');

    /*
|--------------------------------------------------------------------------
| Lịch sử đăng nhập
|--------------------------------------------------------------------------
*/

Route::get(
    '/login-histories',
    [LoginHistoryController::class, 'index']
)->name('login-histories.index');

Route::get(
    '/login-histories/{loginHistory}',
    [LoginHistoryController::class, 'show']
)
    ->whereNumber('loginHistory')
    ->name('login-histories.show');

/*
|--------------------------------------------------------------------------
| Lịch sử tìm kiếm
|--------------------------------------------------------------------------
*/

Route::get(
    '/search-histories',
    [SearchHistoryController::class, 'index']
)->name('search-histories.index');

Route::get(
    '/search-histories/{searchHistory}',
    [SearchHistoryController::class, 'show']
)
    ->whereNumber('searchHistory')
    ->name('search-histories.show');
/*
|--------------------------------------------------------------------------
| Quản lý khách hàng
|--------------------------------------------------------------------------
*/

Route::get(
    '/customers',
    [CustomerController::class, 'index']
)->name('customers.index');

Route::get(
    '/customers/{customer}/edit',
    [CustomerController::class, 'edit']
)
    ->whereNumber('customer')
    ->name('customers.edit');

Route::put(
    '/customers/{customer}',
    [CustomerController::class, 'update']
)
    ->whereNumber('customer')
    ->name('customers.update');

    Route::patch(
    '/customers/{customer}/status',
    [CustomerController::class, 'updateStatus']
)
    ->whereNumber('customer')
    ->name('customers.update-status');
Route::get(
    '/customers/{customer}',
    [CustomerController::class, 'show']
)
    ->whereNumber('customer')
    ->name('customers.show');


    /*
|--------------------------------------------------------------------------
| Quản lý cài đặt
|--------------------------------------------------------------------------
*/

Route::prefix('settings')
    ->name('settings.')
    ->group(function (): void {

        Route::get(
            '/',
            [SettingController::class, 'index']
        )->name('index');

        Route::post(
            '/',
            [SettingController::class, 'store']
        )->name('store');

        Route::post(
            '/defaults',
            [SettingController::class, 'seedDefaults']
        )->name('defaults');

        Route::patch(
            '/groups/{group}',
            [SettingController::class, 'updateGroup']
        )->name('groups.update');

        Route::patch(
            '/{setting}/meta',
            [SettingController::class, 'updateMeta']
        )
            ->whereNumber('setting')
            ->name('meta.update');

        Route::delete(
            '/{setting}',
            [SettingController::class, 'destroy']
        )
            ->whereNumber('setting')
            ->name('destroy');
    });
/*
|--------------------------------------------------------------------------
| Quản lý đánh giá
|--------------------------------------------------------------------------
*/

Route::prefix('reviews')
    ->name('reviews.')
    ->group(function (): void {

        Route::get(
            '/',
            [ReviewController::class, 'index']
        )->name('index');

        Route::patch(
            '/{review}/status',
            [ReviewController::class, 'updateStatus']
        )
            ->whereNumber('review')
            ->name('update-status');

        Route::post(
            '/{review}/replies',
            [ReviewController::class, 'reply']
        )
            ->whereNumber('review')
            ->name('reply');

        Route::delete(
            '/{review}/replies/{reply}',
            [ReviewController::class, 'destroyReply']
        )
            ->whereNumber('review')
            ->whereNumber('reply')
            ->name('replies.destroy');

        Route::get(
            '/{review}',
            [ReviewController::class, 'show']
        )
            ->whereNumber('review')
            ->name('show');
    });

    /*
|--------------------------------------------------------------------------
| Thuộc tính và giá trị biến thể
|--------------------------------------------------------------------------
*/

Route::get(
    '/variant-attributes',
    [VariantAttributeController::class, 'index']
)->name('variant-attributes.index');

Route::get(
    '/variant-attributes/create',
    [VariantAttributeController::class, 'create']
)->name('variant-attributes.create');

Route::post(
    '/variant-attributes',
    [VariantAttributeController::class, 'store']
)->name('variant-attributes.store');

Route::get(
    '/variant-attributes/{variantAttribute}',
    [VariantAttributeController::class, 'show']
)
    ->whereNumber('variantAttribute')
    ->name('variant-attributes.show');

Route::get(
    '/variant-attributes/{variantAttribute}/edit',
    [VariantAttributeController::class, 'edit']
)
    ->whereNumber('variantAttribute')
    ->name('variant-attributes.edit');

Route::put(
    '/variant-attributes/{variantAttribute}',
    [VariantAttributeController::class, 'update']
)
    ->whereNumber('variantAttribute')
    ->name('variant-attributes.update');

Route::delete(
    '/variant-attributes/{variantAttribute}',
    [VariantAttributeController::class, 'destroy']
)
    ->whereNumber('variantAttribute')
    ->name('variant-attributes.destroy');

Route::get(
    '/variant-attributes/{variantAttribute}/values/create',
    [VariantValueController::class, 'create']
)
    ->whereNumber('variantAttribute')
    ->name('variant-values.create');

Route::post(
    '/variant-attributes/{variantAttribute}/values',
    [VariantValueController::class, 'store']
)
    ->whereNumber('variantAttribute')
    ->name('variant-values.store');

Route::get(
    '/variant-values/{variantValue}/edit',
    [VariantValueController::class, 'edit']
)
    ->whereNumber('variantValue')
    ->name('variant-values.edit');

Route::put(
    '/variant-values/{variantValue}',
    [VariantValueController::class, 'update']
)
    ->whereNumber('variantValue')
    ->name('variant-values.update');

Route::delete(
    '/variant-values/{variantValue}',
    [VariantValueController::class, 'destroy']
)
    ->whereNumber('variantValue')
    ->name('variant-values.destroy');
    /*
|--------------------------------------------------------------------------
| Phương thức vận chuyển
|--------------------------------------------------------------------------
*/

Route::get(
    '/shipping-methods',
    [ShippingMethodController::class, 'index']
)->name('shipping-methods.index');

Route::get(
    '/shipping-methods/create',
    [ShippingMethodController::class, 'create']
)->name('shipping-methods.create');

Route::post(
    '/shipping-methods',
    [ShippingMethodController::class, 'store']
)->name('shipping-methods.store');

Route::get(
    '/shipping-methods/{shippingMethod}',
    [ShippingMethodController::class, 'show']
)->name('shipping-methods.show');

Route::get(
    '/shipping-methods/{shippingMethod}/edit',
    [ShippingMethodController::class, 'edit']
)->name('shipping-methods.edit');

Route::put(
    '/shipping-methods/{shippingMethod}',
    [ShippingMethodController::class, 'update']
)->name('shipping-methods.update');

Route::patch(
    '/shipping-methods/{shippingMethod}/toggle',
    [ShippingMethodController::class, 'toggle']
)->name('shipping-methods.toggle');

Route::delete(
    '/shipping-methods/{shippingMethod}',
    [ShippingMethodController::class, 'destroy']
)->name('shipping-methods.destroy');
    /*
|--------------------------------------------------------------------------
| Quản lý hỏi đáp sản phẩm
|--------------------------------------------------------------------------
*/

Route::prefix('questions')
    ->name('questions.')
    ->group(function (): void {

        Route::get(
            '/',
            [QuestionController::class, 'index']
        )->name('index');

        Route::patch(
            '/{question}/status',
            [QuestionController::class, 'updateStatus']
        )
            ->whereNumber('question')
            ->name('update-status');

        Route::post(
            '/{question}/answers',
            [QuestionController::class, 'answer']
        )
            ->whereNumber('question')
            ->name('answer');

        Route::patch(
            '/{question}/answers/{answer}',
            [QuestionController::class, 'updateAnswer']
        )
            ->whereNumber('question')
            ->whereNumber('answer')
            ->name('answers.update');

        Route::delete(
            '/{question}/answers/{answer}',
            [QuestionController::class, 'destroyAnswer']
        )
            ->whereNumber('question')
            ->whereNumber('answer')
            ->name('answers.destroy');

        Route::get(
            '/{question}',
            [QuestionController::class, 'show']
        )
            ->whereNumber('question')
            ->name('show');
    });

/*
|--------------------------------------------------------------------------
| Nhật ký quản trị
|--------------------------------------------------------------------------
*/

Route::prefix('audit-logs')
    ->name('audit-logs.')
    ->group(function (): void {

        Route::get(
            '/',
            [AuditLogController::class, 'index']
        )->name('index');

        Route::get(
            '/{auditLog}',
            [AuditLogController::class, 'show']
        )
            ->whereNumber('auditLog')
            ->name('show');
    });

    /*
|--------------------------------------------------------------------------
| Quản lý trang nội dung
|--------------------------------------------------------------------------
*/

Route::get(
    '/pages',
    [PageController::class, 'index']
)->name('pages.index');

Route::get(
    '/pages/create',
    [PageController::class, 'create']
)->name('pages.create');

Route::post(
    '/pages',
    [PageController::class, 'store']
)->name('pages.store');

Route::get(
    '/pages/{page}',
    [PageController::class, 'show']
)
    ->whereNumber('page')
    ->name('pages.show');

Route::get(
    '/pages/{page}/edit',
    [PageController::class, 'edit']
)
    ->whereNumber('page')
    ->name('pages.edit');

Route::put(
    '/pages/{page}',
    [PageController::class, 'update']
)
    ->whereNumber('page')
    ->name('pages.update');

Route::delete(
    '/pages/{page}',
    [PageController::class, 'destroy']
)
    ->whereNumber('page')
    ->name('pages.destroy');
    /*
|--------------------------------------------------------------------------
| Quản lý Newsletter
|--------------------------------------------------------------------------
*/

Route::prefix('newsletter-subscribers')
    ->name('newsletter-subscribers.')
    ->group(function (): void {

        Route::get(
            '/',
            [NewsletterSubscriberController::class, 'index']
        )->name('index');

        Route::post(
            '/',
            [NewsletterSubscriberController::class, 'store']
        )->name('store');

        Route::patch(
            '/{subscriber}/status',
            [NewsletterSubscriberController::class, 'updateStatus']
        )
            ->whereNumber('subscriber')
            ->name('update-status');

        Route::patch(
            '/{subscriber}/verify',
            [NewsletterSubscriberController::class, 'verify']
        )
            ->whereNumber('subscriber')
            ->name('verify');

        Route::delete(
            '/{subscriber}',
            [NewsletterSubscriberController::class, 'destroy']
        )
            ->whereNumber('subscriber')
            ->name('destroy');

        Route::get(
            '/{subscriber}',
            [NewsletterSubscriberController::class, 'show']
        )
            ->whereNumber('subscriber')
            ->name('show');
    });
    /*
|--------------------------------------------------------------------------
| Quản lý liên hệ
|--------------------------------------------------------------------------
*/

Route::prefix('contact-messages')
    ->name('contact-messages.')
    ->group(function (): void {

        Route::get(
            '/',
            [ContactMessageController::class, 'index']
        )->name('index');

        Route::patch(
            '/{contactMessage}/status',
            [ContactMessageController::class, 'updateStatus']
        )
            ->whereNumber('contactMessage')
            ->name('update-status');

        Route::patch(
            '/{contactMessage}/note',
            [ContactMessageController::class, 'updateNote']
        )
            ->whereNumber('contactMessage')
            ->name('update-note');

        Route::patch(
            '/{contactMessage}/assign-to-me',
            [ContactMessageController::class, 'assignToMe']
        )
            ->whereNumber('contactMessage')
            ->name('assign-to-me');

        Route::patch(
            '/{contactMessage}/mark-replied',
            [ContactMessageController::class, 'markReplied']
        )
            ->whereNumber('contactMessage')
            ->name('mark-replied');

        Route::patch(
            '/{contactMessage}/close',
            [ContactMessageController::class, 'close']
        )
            ->whereNumber('contactMessage')
            ->name('close');

        Route::patch(
            '/{contactMessage}/spam',
            [ContactMessageController::class, 'markSpam']
        )
            ->whereNumber('contactMessage')
            ->name('mark-spam');

        Route::get(
            '/{contactMessage}',
            [ContactMessageController::class, 'show']
        )
            ->whereNumber('contactMessage')
            ->name('show');
    });
   /*
|--------------------------------------------------------------------------
| Quản lý Loyalty
|--------------------------------------------------------------------------
*/

Route::prefix('loyalty')
    ->name('loyalty.')
    ->group(function (): void {

        Route::get(
            '/',
            [LoyaltyController::class, 'index']
        )->name('index');

        Route::get(
            '/tiers',
            [LoyaltyController::class, 'tiers']
        )->name('tiers');

        Route::patch(
            '/tiers/{tier}',
            [LoyaltyController::class, 'updateTierSetting']
        )
            ->whereNumber('tier')
            ->name('tiers.update');

        Route::patch(
            '/{account}/points',
            [LoyaltyController::class, 'adjustPoints']
        )
            ->whereNumber('account')
            ->name('adjust-points');

        Route::patch(
            '/{account}/tier',
            [LoyaltyController::class, 'updateTier']
        )
            ->whereNumber('account')
            ->name('update-tier');

        Route::get(
            '/{account}',
            [LoyaltyController::class, 'show']
        )
            ->whereNumber('account')
            ->name('show');
    });

    /*
|--------------------------------------------------------------------------
| Thông báo quản trị
|--------------------------------------------------------------------------
*/

Route::prefix('notifications')
    ->name('notifications.')
    ->group(function (): void {

        Route::get(
            '/',
            [NotificationController::class, 'index']
        )->name('index');

        Route::get(
            '/latest',
            [NotificationController::class, 'latest']
        )->name('latest');

        Route::get(
            '/unread-count',
            [NotificationController::class, 'unreadCount']
        )->name('unread-count');

        Route::patch(
            '/read-all',
            [NotificationController::class, 'markAllAsRead']
        )->name('read-all');

        Route::get(
            '/{notification}/open',
            [NotificationController::class, 'open']
        )->name('open');

        Route::patch(
            '/{notification}/read',
            [NotificationController::class, 'markAsRead']
        )->name('read');

        Route::patch(
            '/{notification}/unread',
            [NotificationController::class, 'markAsUnread']
        )->name('unread');

        Route::delete(
            '/{notification}',
            [NotificationController::class, 'destroy']
        )->name('destroy');
    });
        /*
        |--------------------------------------------------------------------------
        | Quản lý danh mục
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/categories',
            [CategoryController::class, 'index']
        )->name('categories.index');

        Route::get(
            '/categories/create',
            [CategoryController::class, 'create']
        )->name('categories.create');

        Route::post(
            '/categories',
            [CategoryController::class, 'store']
        )->name('categories.store');

        Route::get(
            '/categories/{category}/edit',
            [CategoryController::class, 'edit']
        )
            ->whereNumber('category')
            ->name('categories.edit');

        Route::put(
            '/categories/{category}',
            [CategoryController::class, 'update']
        )
            ->whereNumber('category')
            ->name('categories.update');

        Route::delete(
            '/categories/{category}',
            [CategoryController::class, 'destroy']
        )
            ->whereNumber('category')
            ->name('categories.destroy');

        Route::get(
            '/categories/{category}',
            [CategoryController::class, 'show']
        )
            ->whereNumber('category')
            ->name('categories.show');
    });

