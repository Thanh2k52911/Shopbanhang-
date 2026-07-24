<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CouponCategorySeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'SKINCARE15' => [
                'cham-soc-da-mat',
                'skincare',
                'sua-rua-mat',
                'serum',
                'kem-duong',
            ],

            'FREESHIP' => [
                'cham-soc-da-mat',
                'trang-diem',
                'makeup',
                'cham-soc-co-the',
            ],
        ];

        foreach ($data as $couponCode => $categorySlugs) {
            $couponId = DB::table('coupons')
                ->where('code', $couponCode)
                ->value('id');

            if (!$couponId) {
                $this->command?->warn(
                    "Không tìm thấy coupon: {$couponCode}"
                );

                continue;
            }

            foreach ($categorySlugs as $slug) {
                $categoryId = DB::table('categories')
                    ->where('slug', $slug)
                    ->value('id');

                if (!$categoryId) {
                    continue;
                }

                DB::table('coupon_categories')->updateOrInsert([
                    'coupon_id' => $couponId,
                    'category_id' => $categoryId,
                ]);
            }
        }
    }
}
