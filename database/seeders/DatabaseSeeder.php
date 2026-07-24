<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // Phân quyền
            RoleSeeder::class,
            PermissionSeeder::class,
            RolePermissionSeeder::class,

            // Người dùng
            UserSeeder::class,
            UserRoleSeeder::class,

            // Dữ liệu nền sản phẩm
            CategorySeeder::class,
            BrandSeeder::class,
            SupplierSeeder::class,
            WarehouseSeeder::class,
            VariantAttributeSeeder::class,
            VariantValueSeeder::class,

            // Sản phẩm
            ProductSeeder::class,
            ProductSeeder::class,
            ProductImageSeeder::class,
            ProductVideoSeeder::class,
            ProductVariantSeeder::class,
            ProductSkuSeeder::class,

            // Kho hàng
            InventorySeeder::class,

            // Khuyến mãi và thống kê
            DiscountCampaignSeeder::class,
            ProductDiscountSeeder::class,
            ProductStatisticSeeder::class,

            // Sản phẩm yêu thích
            ProductFavoriteSeeder::class,

            // Giỏ hàng
            CartSeeder::class,
            CartItemSeeder::class,

            // Coupon
            CouponSeeder::class,
            CouponProductSeeder::class,
            CouponCategorySeeder::class,
            CouponUserSeeder::class,

            // Đơn hàng
            OrderSeeder::class,
            OrderAddressSeeder::class,
            OrderItemSeeder::class,

            // Sử dụng coupon
            CouponUsageSeeder::class,

            // Thanh toán
            PaymentSeeder::class,
            PaymentTransactionSeeder::class,

            // Vận chuyển
            ShippingMethodSeeder::class,
            ShipmentSeeder::class,
            ShipmentItemSeeder::class,
            ShipmentStatusHistorySeeder::class,

            // Lịch sử kho
            InventoryTransactionSeeder::class,

            // Đánh giá sản phẩm
            ProductReviewSeeder::class,
            ReviewImageSeeder::class,
            ReviewLikeSeeder::class,
            ReviewReplySeeder::class,

            // Sản phẩm đã xem gần đây
            RecentlyViewedProductSeeder::class,

            // Lịch sử tìm kiếm
            SearchHistorySeeder::class,

            // Hỏi đáp sản phẩm
            ProductQuestionSeeder::class,
            ProductQuestionAnswerSeeder::class,

            // Hạng thành viên và điểm thưởng
            LoyaltyTierSeeder::class,
            LoyaltyAccountSeeder::class,
            LoyaltyTransactionSeeder::class,

            // Đổi trả
            ReturnRequestSeeder::class,
            ReturnRequestItemSeeder::class,
            ReturnRequestImageSeeder::class,
            ReturnStatusHistorySeeder::class,
            RefundSeeder::class,

            // Liên hệ
            ContactMessageSeeder::class,

            // Newsletter
            NewsletterSubscriberSeeder::class,

            // Nội dung website
            BannerSeeder::class,
            PageSeeder::class,

            // Cấu hình hệ thống
            SettingSeeder::class,

            // Lịch sử hệ thống
            LoginHistorySeeder::class,
            AuditLogSeeder::class,

            // Thông báo
            NotificationSeeder::class,

            // Lịch sử trạng thái đơn hàng
            OrderStatusHistorySeeder::class,
        ]);
    }
}
