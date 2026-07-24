<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BannerSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('users')
            ->orderBy('id')
            ->value('id');

        $banners = [
            [
                'name' => 'Banner chăm sóc da mùa hè',
                'title' => 'Làn da khỏe đẹp mùa hè',
                'subtitle' => 'Ưu đãi đến 20% cho sản phẩm chống nắng và dưỡng da',
                'desktop_image' => 'banners/home-summer-skincare-desktop.jpg',
                'mobile_image' => 'banners/home-summer-skincare-mobile.jpg',
                'link_url' => '/products?campaign=summer-skincare',
                'button_text' => 'Mua ngay',
                'position' => 'home_slider',
                'target' => '_self',
                'sort_order' => 1,
                'status' => true,
                'start_at' => now()->subDays(5),
                'end_at' => now()->addMonths(2),
            ],
            [
                'name' => 'Banner thương hiệu CeraVe',
                'title' => 'Chăm sóc hàng rào bảo vệ da',
                'subtitle' => 'Khám phá các sản phẩm nổi bật từ CeraVe',
                'desktop_image' => 'banners/cerave-desktop.jpg',
                'mobile_image' => 'banners/cerave-mobile.jpg',
                'link_url' => '/brands/cerave',
                'button_text' => 'Xem sản phẩm',
                'position' => 'home_slider',
                'target' => '_self',
                'sort_order' => 2,
                'status' => true,
                'start_at' => now()->subDays(10),
                'end_at' => now()->addMonths(3),
            ],
            [
                'name' => 'Banner flash sale',
                'title' => 'Flash Sale cuối tuần',
                'subtitle' => 'Số lượng có hạn, ưu đãi không chờ đợi',
                'desktop_image' => 'banners/flash-sale-desktop.jpg',
                'mobile_image' => 'banners/flash-sale-mobile.jpg',
                'link_url' => '/flash-sale',
                'button_text' => 'Săn ưu đãi',
                'position' => 'home_middle',
                'target' => '_self',
                'sort_order' => 1,
                'status' => true,
                'start_at' => now(),
                'end_at' => now()->addDays(7),
            ],
            [
                'name' => 'Banner mỹ phẩm Hàn Quốc',
                'title' => 'K-Beauty chính hãng',
                'subtitle' => 'Tinh hoa chăm sóc da từ Hàn Quốc',
                'desktop_image' => 'banners/kbeauty-desktop.jpg',
                'mobile_image' => 'banners/kbeauty-mobile.jpg',
                'link_url' => '/products?origin=han-quoc',
                'button_text' => 'Khám phá',
                'position' => 'home_bottom',
                'target' => '_self',
                'sort_order' => 1,
                'status' => true,
                'start_at' => null,
                'end_at' => null,
            ],
            [
                'name' => 'Popup khách hàng mới',
                'title' => 'Chào mừng bạn đến với Cosmetic Shop',
                'subtitle' => 'Nhập mã WELCOME10 để nhận ưu đãi cho đơn đầu tiên',
                'desktop_image' => 'banners/welcome-popup.jpg',
                'mobile_image' => null,
                'link_url' => '/register',
                'button_text' => 'Đăng ký ngay',
                'position' => 'popup',
                'target' => '_self',
                'sort_order' => 1,
                'status' => true,
                'start_at' => now()->subDay(),
                'end_at' => now()->addMonths(6),
            ],
        ];

        foreach ($banners as $banner) {
            DB::table('banners')->updateOrInsert(
                [
                    'name' => $banner['name'],
                    'position' => $banner['position'],
                ],
                array_merge($banner, [
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'deleted_at' => null,
                ])
            );
        }
    }
}
