<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'group' => 'general',
                'key' => 'site_name',
                'value' => 'Cosmetic Shop',
                'type' => 'string',
                'label' => 'Tên website',
                'description' => 'Tên hiển thị chính của website.',
                'is_public' => true,
                'sort_order' => 1,
            ],
            [
                'group' => 'general',
                'key' => 'site_logo',
                'value' => 'settings/logo.png',
                'type' => 'image',
                'label' => 'Logo website',
                'description' => 'Logo chính của Cosmetic Shop.',
                'is_public' => true,
                'sort_order' => 2,
            ],
            [
                'group' => 'general',
                'key' => 'site_favicon',
                'value' => 'settings/favicon.png',
                'type' => 'image',
                'label' => 'Favicon',
                'description' => 'Biểu tượng hiển thị trên trình duyệt.',
                'is_public' => true,
                'sort_order' => 3,
            ],
            [
                'group' => 'contact',
                'key' => 'hotline',
                'value' => '1900 0000',
                'type' => 'string',
                'label' => 'Hotline',
                'description' => 'Số điện thoại hỗ trợ khách hàng.',
                'is_public' => true,
                'sort_order' => 1,
            ],
            [
                'group' => 'contact',
                'key' => 'contact_email',
                'value' => 'support@cosmeticshop.vn',
                'type' => 'string',
                'label' => 'Email liên hệ',
                'description' => 'Email tiếp nhận hỗ trợ khách hàng.',
                'is_public' => true,
                'sort_order' => 2,
            ],
            [
                'group' => 'contact',
                'key' => 'shop_address',
                'value' => 'Số 1 Đại Cồ Việt, Hai Bà Trưng, Hà Nội',
                'type' => 'text',
                'label' => 'Địa chỉ cửa hàng',
                'description' => 'Địa chỉ cửa hàng chính.',
                'is_public' => true,
                'sort_order' => 3,
            ],
            [
                'group' => 'social',
                'key' => 'facebook_url',
                'value' => 'https://facebook.com/cosmeticshop',
                'type' => 'string',
                'label' => 'Facebook',
                'description' => 'Trang Facebook của cửa hàng.',
                'is_public' => true,
                'sort_order' => 1,
            ],
            [
                'group' => 'social',
                'key' => 'instagram_url',
                'value' => 'https://instagram.com/cosmeticshop',
                'type' => 'string',
                'label' => 'Instagram',
                'description' => 'Trang Instagram của cửa hàng.',
                'is_public' => true,
                'sort_order' => 2,
            ],
            [
                'group' => 'payment',
                'key' => 'cod_enabled',
                'value' => '1',
                'type' => 'boolean',
                'label' => 'Bật thanh toán COD',
                'description' => 'Cho phép thanh toán khi nhận hàng.',
                'is_public' => true,
                'sort_order' => 1,
            ],
            [
                'group' => 'payment',
                'key' => 'bank_transfer_enabled',
                'value' => '1',
                'type' => 'boolean',
                'label' => 'Bật chuyển khoản',
                'description' => 'Cho phép khách hàng thanh toán chuyển khoản.',
                'is_public' => true,
                'sort_order' => 2,
            ],
            [
                'group' => 'shipping',
                'key' => 'default_shipping_fee',
                'value' => '30000',
                'type' => 'number',
                'label' => 'Phí vận chuyển mặc định',
                'description' => 'Phí giao hàng mặc định tính theo VND.',
                'is_public' => true,
                'sort_order' => 1,
            ],
            [
                'group' => 'shipping',
                'key' => 'free_shipping_minimum',
                'value' => '500000',
                'type' => 'number',
                'label' => 'Mức miễn phí vận chuyển',
                'description' => 'Giá trị đơn tối thiểu để được miễn phí vận chuyển.',
                'is_public' => true,
                'sort_order' => 2,
            ],
            [
                'group' => 'seo',
                'key' => 'meta_title',
                'value' => 'Cosmetic Shop - Mỹ phẩm chính hãng',
                'type' => 'string',
                'label' => 'Tiêu đề SEO',
                'description' => 'Tiêu đề mặc định cho website.',
                'is_public' => true,
                'sort_order' => 1,
            ],
            [
                'group' => 'seo',
                'key' => 'meta_description',
                'value' => 'Mua mỹ phẩm chăm sóc da, trang điểm và chăm sóc cơ thể chính hãng.',
                'type' => 'text',
                'label' => 'Mô tả SEO',
                'description' => 'Mô tả SEO mặc định cho website.',
                'is_public' => true,
                'sort_order' => 2,
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->updateOrInsert(
                [
                    'key' => $setting['key'],
                ],
                array_merge($setting, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
