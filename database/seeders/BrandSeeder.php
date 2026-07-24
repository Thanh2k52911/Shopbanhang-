<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $brands = [
                [
                    'name' => 'La Roche-Posay',
                    'country' => 'Pháp',
                    'website' => 'https://www.laroche-posay.com',
                    'description' => 'Thương hiệu dược mỹ phẩm nổi tiếng với các sản phẩm chăm sóc da nhạy cảm và da mụn.',
                ],
                [
                    'name' => 'CeraVe',
                    'country' => 'Mỹ',
                    'website' => 'https://www.cerave.com',
                    'description' => 'Thương hiệu chăm sóc da nổi bật với công nghệ ceramide giúp phục hồi hàng rào bảo vệ da.',
                ],
                [
                    'name' => 'Bioderma',
                    'country' => 'Pháp',
                    'website' => 'https://www.bioderma.com',
                    'description' => 'Dược mỹ phẩm chuyên về làm sạch, dưỡng da và chăm sóc làn da nhạy cảm.',
                ],
                [
                    'name' => 'Vichy',
                    'country' => 'Pháp',
                    'website' => 'https://www.vichy.com',
                    'description' => 'Thương hiệu dược mỹ phẩm sử dụng nước khoáng núi lửa trong các sản phẩm chăm sóc da.',
                ],
                [
                    'name' => 'Eucerin',
                    'country' => 'Đức',
                    'website' => 'https://www.eucerin.com',
                    'description' => 'Thương hiệu dược mỹ phẩm chuyên giải quyết các vấn đề da liễu.',
                ],
                [
                    'name' => 'Avène',
                    'country' => 'Pháp',
                    'website' => 'https://www.eau-thermale-avene.com',
                    'description' => 'Dược mỹ phẩm dành cho da nhạy cảm với thành phần nước khoáng Avène.',
                ],
                [
                    'name' => 'SVR',
                    'country' => 'Pháp',
                    'website' => 'https://www.labo-svr.com',
                    'description' => 'Dược mỹ phẩm với các công thức hoạt tính cao dành cho nhiều tình trạng da.',
                ],
                [
                    'name' => 'Paula’s Choice',
                    'country' => 'Mỹ',
                    'website' => 'https://www.paulaschoice.com',
                    'description' => 'Thương hiệu chăm sóc da nổi bật với BHA, retinol và các sản phẩm đặc trị.',
                ],
                [
                    'name' => 'The Ordinary',
                    'country' => 'Canada',
                    'website' => 'https://theordinary.com',
                    'description' => 'Thương hiệu mỹ phẩm tập trung vào các hoạt chất chăm sóc da với công thức đơn giản.',
                ],
                [
                    'name' => 'Skin1004',
                    'country' => 'Hàn Quốc',
                    'website' => 'https://skin1004.com',
                    'description' => 'Thương hiệu nổi tiếng với các sản phẩm chứa rau má Madagascar.',
                ],
                [
                    'name' => 'Anua',
                    'country' => 'Hàn Quốc',
                    'website' => 'https://anua.kr',
                    'description' => 'Thương hiệu chăm sóc da dịu nhẹ nổi bật với chiết xuất diếp cá.',
                ],
                [
                    'name' => 'Some By Mi',
                    'country' => 'Hàn Quốc',
                    'website' => 'https://somebymi.com',
                    'description' => 'Thương hiệu chăm sóc da tập trung vào các sản phẩm hỗ trợ da mụn.',
                ],
                [
                    'name' => 'COSRX',
                    'country' => 'Hàn Quốc',
                    'website' => 'https://www.cosrx.com',
                    'description' => 'Thương hiệu mỹ phẩm Hàn Quốc nổi tiếng với các sản phẩm chăm sóc da mụn và da nhạy cảm.',
                ],
                [
                    'name' => 'Beauty of Joseon',
                    'country' => 'Hàn Quốc',
                    'website' => 'https://beautyofjoseon.com',
                    'description' => 'Thương hiệu kết hợp nguyên liệu truyền thống Hàn Quốc với công nghệ chăm sóc da hiện đại.',
                ],
                [
                    'name' => 'Laneige',
                    'country' => 'Hàn Quốc',
                    'website' => 'https://www.laneige.com',
                    'description' => 'Thương hiệu nổi bật với các sản phẩm cấp ẩm và mặt nạ ngủ.',
                ],
                [
                    'name' => 'Innisfree',
                    'country' => 'Hàn Quốc',
                    'website' => 'https://www.innisfree.com',
                    'description' => 'Thương hiệu mỹ phẩm thiên nhiên lấy cảm hứng từ đảo Jeju.',
                ],
                [
                    'name' => 'Etude',
                    'country' => 'Hàn Quốc',
                    'website' => 'https://www.etude.com',
                    'description' => 'Thương hiệu mỹ phẩm trang điểm và chăm sóc da dành cho giới trẻ.',
                ],
                [
                    'name' => 'Rom&nd',
                    'country' => 'Hàn Quốc',
                    'website' => 'https://romand.co.kr',
                    'description' => 'Thương hiệu trang điểm nổi tiếng với son môi và các sản phẩm màu sắc.',
                ],
                [
                    'name' => 'Clio',
                    'country' => 'Hàn Quốc',
                    'website' => 'https://www.clubclio.co.kr',
                    'description' => 'Thương hiệu trang điểm nổi bật với cushion, phấn mắt và kẻ mắt.',
                ],
                [
                    'name' => 'Peripera',
                    'country' => 'Hàn Quốc',
                    'website' => 'https://www.clubclio.co.kr',
                    'description' => 'Thương hiệu trang điểm trẻ trung nổi tiếng với các dòng son tint.',
                ],
                [
                    'name' => 'Hada Labo',
                    'country' => 'Nhật Bản',
                    'website' => 'https://www.hadalabo.com',
                    'description' => 'Thương hiệu chăm sóc da nổi bật với các sản phẩm chứa hyaluronic acid.',
                ],
                [
                    'name' => 'Senka',
                    'country' => 'Nhật Bản',
                    'website' => 'https://www.senka.id',
                    'description' => 'Thương hiệu chăm sóc da phổ biến với các sản phẩm làm sạch.',
                ],
                [
                    'name' => 'Biore',
                    'country' => 'Nhật Bản',
                    'website' => 'https://www.kao.com',
                    'description' => 'Thương hiệu nổi tiếng với sữa rửa mặt và kem chống nắng.',
                ],
                [
                    'name' => 'Shiseido',
                    'country' => 'Nhật Bản',
                    'website' => 'https://www.shiseido.com',
                    'description' => 'Thương hiệu mỹ phẩm cao cấp lâu đời của Nhật Bản.',
                ],
                [
                    'name' => 'Kose',
                    'country' => 'Nhật Bản',
                    'website' => 'https://www.kose.co.jp',
                    'description' => 'Thương hiệu mỹ phẩm Nhật Bản với nhiều dòng chăm sóc da và trang điểm.',
                ],
                [
                    'name' => 'Simple',
                    'country' => 'Anh',
                    'website' => 'https://www.simple.co.uk',
                    'description' => 'Thương hiệu chăm sóc da dịu nhẹ, phù hợp với da nhạy cảm.',
                ],
                [
                    'name' => 'Garnier',
                    'country' => 'Pháp',
                    'website' => 'https://www.garnier.com',
                    'description' => 'Thương hiệu phổ biến với các sản phẩm chăm sóc da, tóc và làm sạch.',
                ],
                [
                    'name' => 'L’Oréal Paris',
                    'country' => 'Pháp',
                    'website' => 'https://www.lorealparis.com',
                    'description' => 'Thương hiệu mỹ phẩm toàn cầu với sản phẩm chăm sóc da, tóc và trang điểm.',
                ],
                [
                    'name' => 'Maybelline',
                    'country' => 'Mỹ',
                    'website' => 'https://www.maybelline.com',
                    'description' => 'Thương hiệu trang điểm phổ biến với mascara, kem nền và son môi.',
                ],
                [
                    'name' => 'Nivea',
                    'country' => 'Đức',
                    'website' => 'https://www.nivea.com',
                    'description' => 'Thương hiệu chăm sóc da và cơ thể lâu đời.',
                ],
                [
                    'name' => 'Dove',
                    'country' => 'Mỹ',
                    'website' => 'https://www.dove.com',
                    'description' => 'Thương hiệu chăm sóc cơ thể, tóc và vệ sinh cá nhân.',
                ],
                [
                    'name' => 'Vaseline',
                    'country' => 'Mỹ',
                    'website' => 'https://www.vaseline.com',
                    'description' => 'Thương hiệu dưỡng da và dưỡng thể nổi tiếng.',
                ],
                [
                    'name' => 'Cetaphil',
                    'country' => 'Canada',
                    'website' => 'https://www.cetaphil.com',
                    'description' => 'Thương hiệu chăm sóc da dịu nhẹ được sử dụng phổ biến cho da nhạy cảm.',
                ],
                [
                    'name' => 'Neutrogena',
                    'country' => 'Mỹ',
                    'website' => 'https://www.neutrogena.com',
                    'description' => 'Thương hiệu chăm sóc da nổi tiếng với các sản phẩm làm sạch và cấp ẩm.',
                ],
                [
                    'name' => 'Rohto',
                    'country' => 'Nhật Bản',
                    'website' => 'https://www.rohto.com',
                    'description' => 'Tập đoàn mỹ phẩm và dược phẩm sở hữu nhiều dòng chăm sóc da phổ biến.',
                ],
                [
                    'name' => 'Cocoon',
                    'country' => 'Việt Nam',
                    'website' => 'https://cocoonvietnam.com',
                    'description' => 'Thương hiệu mỹ phẩm thuần chay Việt Nam sử dụng nguyên liệu bản địa.',
                ],
                [
                    'name' => 'Thorakao',
                    'country' => 'Việt Nam',
                    'website' => null,
                    'description' => 'Thương hiệu mỹ phẩm Việt Nam lâu đời với các sản phẩm từ thiên nhiên.',
                ],
                [
                    'name' => 'Lemonade',
                    'country' => 'Việt Nam',
                    'website' => 'https://lemonade.vn',
                    'description' => 'Thương hiệu trang điểm Việt Nam với các sản phẩm dành cho phụ nữ châu Á.',
                ],
                [
                    'name' => 'Ofélia',
                    'country' => 'Việt Nam',
                    'website' => 'https://ofelia.vn',
                    'description' => 'Thương hiệu trang điểm Việt Nam nổi bật với son môi và sản phẩm màu sắc.',
                ],
                [
                    'name' => 'Black Rouge',
                    'country' => 'Hàn Quốc',
                    'website' => 'https://blackrouge.co.kr',
                    'description' => 'Thương hiệu trang điểm nổi bật với các dòng son tint.',
                ],
            ];

            foreach ($brands as $index => $brandData) {
                $slug = Str::slug($brandData['name']);

                Brand::withTrashed()->updateOrCreate(
                    [
                        'slug' => $slug,
                    ],
                    [
                        'name' => $brandData['name'],
                        'thumbnail' => "brands/{$slug}.png",
                        'country' => $brandData['country'],
                        'website' => $brandData['website'],
                        'description' => $brandData['description'],
                        'sort_order' => $index + 1,
                        'status' => 1,
                        'deleted_at' => null,
                    ]
                );
            }
        });
    }
}
