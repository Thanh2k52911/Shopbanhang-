<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CouponProductSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'SKINCARE15' => [
                'sua-rua-mat-cerave-foaming-facial-cleanser',
                'sua-rua-mat-senka-perfect-whip',
                'nuoc-tay-trang-bioderma-sensibio-h2o',
                'serum-the-ordinary-niacinamide-10-zinc-1',
                'kem-duong-am-cerave-moisturizing-cream',
            ],

            'GIAM50K' => [
                'sua-chong-nang-anessa-perfect-uv-sunscreen',
                'kem-chong-nang-la-roche-posay-anthelios',
                'mat-na-ngu-laneige-water-sleeping-mask',
            ],
        ];

        foreach ($data as $couponCode => $productSlugs) {
            $couponId = DB::table('coupons')
                ->where('code', $couponCode)
                ->value('id');

            if (!$couponId) {
                $this->command?->warn(
                    "Không tìm thấy coupon: {$couponCode}"
                );

                continue;
            }

            foreach ($productSlugs as $slug) {
                $productId = DB::table('products')
                    ->where('slug', $slug)
                    ->value('id');

                if (!$productId) {
                    $this->command?->warn(
                        "Không tìm thấy sản phẩm: {$slug}"
                    );

                    continue;
                }

                DB::table('coupon_products')->updateOrInsert([
                    'coupon_id' => $couponId,
                    'product_id' => $productId,
                ]);
            }
        }
    }
}
