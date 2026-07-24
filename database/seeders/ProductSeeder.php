<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        /*
         * Lấy một danh mục phù hợp.
         * Nếu không tìm thấy slug mong muốn thì lấy danh mục đầu tiên
         * để tránh phụ thuộc cứng vào ID.
         */
        $getCategoryId = function (array $slugs): int {
            $categoryId = DB::table('categories')
                ->whereIn('slug', $slugs)
                ->orderByRaw(
                    'FIELD(slug, ' .
                    implode(',', array_fill(0, count($slugs), '?')) .
                    ')',
                    $slugs
                )
                ->value('id');

            $categoryId ??= DB::table('categories')
                ->where('status', 1)
                ->orderBy('id')
                ->value('id');

            if (!$categoryId) {
                throw new RuntimeException(
                    'Không có dữ liệu categories. Hãy chạy CategorySeeder trước.'
                );
            }

            return (int) $categoryId;
        };

        /*
         * Brand và Supplier cho phép null trong migration.
         * Seeder tìm theo slug/tên, không hardcode ID.
         */
        $getBrandId = function (array $slugs): ?int {
            $id = DB::table('brands')
                ->whereIn('slug', $slugs)
                ->orderBy('id')
                ->value('id');

            return $id ? (int) $id : null;
        };

        $getSupplierId = function (array $keywords): ?int {
            foreach ($keywords as $keyword) {
                $id = DB::table('suppliers')
                    ->where('name', 'like', '%' . $keyword . '%')
                    ->orderBy('id')
                    ->value('id');

                if ($id) {
                    return (int) $id;
                }
            }

            return DB::table('suppliers')
                ->where('status', 1)
                ->orderBy('id')
                ->value('id');
        };

        $createdBy = DB::table('users')
            ->orderBy('id')
            ->value('id');

        $products = [
            [
                'category_slugs' => [
                    'sua-rua-mat',
                    'cham-soc-da-mat',
                    'skincare',
                ],
                'brand_slugs' => ['cerave'],
                'supplier_keywords' => ['L’Oréal', 'DKSH'],
                'name' => 'Sữa Rửa Mặt CeraVe Foaming Facial Cleanser',
                'slug' => 'sua-rua-mat-cerave-foaming-facial-cleanser',
                'short_description' => 'Sữa rửa mặt tạo bọt giúp làm sạch dầu thừa và bụi bẩn mà không làm khô da.',
                'description' => 'CeraVe Foaming Facial Cleanser làm sạch da dịu nhẹ, hỗ trợ duy trì hàng rào bảo vệ tự nhiên của da và phù hợp sử dụng hằng ngày.',
                'ingredient' => 'Ceramide, Niacinamide, Hyaluronic Acid và các chất làm sạch dịu nhẹ.',
                'usage' => 'Làm ướt mặt, lấy lượng vừa đủ, massage nhẹ nhàng rồi rửa sạch với nước.',
                'skin_type' => 'Da thường, da dầu và da hỗn hợp',
                'origin' => 'Hoa Kỳ',
                'status' => 1,
                'is_featured' => 1,
                'view_count' => 1250,
            ],
            [
                'category_slugs' => [
                    'sua-rua-mat',
                    'cham-soc-da-mat',
                    'skincare',
                ],
                'brand_slugs' => ['senka'],
                'supplier_keywords' => ['Rohto', 'Nhật Bản'],
                'name' => 'Sữa Rửa Mặt Senka Perfect Whip',
                'slug' => 'sua-rua-mat-senka-perfect-whip',
                'short_description' => 'Sữa rửa mặt tạo bọt mịn giúp loại bỏ bụi bẩn và dầu thừa.',
                'description' => 'Senka Perfect Whip tạo lớp bọt dày mịn, giúp làm sạch bề mặt da và mang lại cảm giác thông thoáng.',
                'ingredient' => 'Tơ tằm trắng, Hyaluronic Acid và Glycerin.',
                'usage' => 'Tạo bọt với nước, massage nhẹ trên mặt trong khoảng 30 giây rồi rửa sạch.',
                'skin_type' => 'Da thường và da hỗn hợp',
                'origin' => 'Nhật Bản',
                'status' => 1,
                'is_featured' => 1,
                'view_count' => 1680,
            ],
            [
                'category_slugs' => [
                    'nuoc-tay-trang',
                    'tay-trang',
                    'cham-soc-da-mat',
                ],
                'brand_slugs' => ['bioderma'],
                'supplier_keywords' => ['DKSH', 'Pháp'],
                'name' => 'Nước Tẩy Trang Bioderma Sensibio H2O',
                'slug' => 'nuoc-tay-trang-bioderma-sensibio-h2o',
                'short_description' => 'Nước tẩy trang dịu nhẹ dành cho làn da nhạy cảm.',
                'description' => 'Bioderma Sensibio H2O sử dụng công nghệ micellar giúp làm sạch lớp trang điểm và bụi bẩn mà vẫn tạo cảm giác dễ chịu trên da.',
                'ingredient' => 'Micellar Water, chiết xuất dưa leo và các thành phần làm dịu.',
                'usage' => 'Thấm sản phẩm vào bông tẩy trang và lau nhẹ toàn bộ khuôn mặt.',
                'skin_type' => 'Da nhạy cảm',
                'origin' => 'Pháp',
                'status' => 1,
                'is_featured' => 1,
                'view_count' => 2100,
            ],
            [
                'category_slugs' => [
                    'kem-chong-nang',
                    'chong-nang',
                    'cham-soc-da-mat',
                ],
                'brand_slugs' => ['anessa'],
                'supplier_keywords' => ['Nhật Bản', 'Guardian'],
                'name' => 'Sữa Chống Nắng Anessa Perfect UV Sunscreen',
                'slug' => 'sua-chong-nang-anessa-perfect-uv-sunscreen',
                'short_description' => 'Sữa chống nắng bảo vệ da trước tia UVA và UVB.',
                'description' => 'Anessa Perfect UV Sunscreen có kết cấu mỏng nhẹ, khả năng chống nước và phù hợp sử dụng khi hoạt động ngoài trời.',
                'ingredient' => 'Zinc Oxide, Titanium Dioxide và các màng lọc chống nắng.',
                'usage' => 'Lắc kỹ, thoa đều lên da trước khi ra nắng khoảng 15 phút.',
                'skin_type' => 'Mọi loại da',
                'origin' => 'Nhật Bản',
                'status' => 1,
                'is_featured' => 1,
                'view_count' => 1820,
            ],
            [
                'category_slugs' => [
                    'kem-chong-nang',
                    'chong-nang',
                    'cham-soc-da-mat',
                ],
                'brand_slugs' => ['la-roche-posay'],
                'supplier_keywords' => ['L’Oréal', 'DKSH'],
                'name' => 'Kem Chống Nắng La Roche-Posay Anthelios',
                'slug' => 'kem-chong-nang-la-roche-posay-anthelios',
                'short_description' => 'Kem chống nắng phổ rộng dành cho da dầu và da nhạy cảm.',
                'description' => 'La Roche-Posay Anthelios giúp bảo vệ da khỏi tác động của ánh nắng và hạn chế cảm giác bóng nhờn.',
                'ingredient' => 'Mexoryl, nước khoáng La Roche-Posay và các màng lọc chống nắng.',
                'usage' => 'Thoa lượng vừa đủ trước khi ra nắng và thoa lại khi cần thiết.',
                'skin_type' => 'Da dầu và da nhạy cảm',
                'origin' => 'Pháp',
                'status' => 1,
                'is_featured' => 1,
                'view_count' => 2380,
            ],
            [
                'category_slugs' => [
                    'serum',
                    'tinh-chat',
                    'cham-soc-da-mat',
                ],
                'brand_slugs' => ['the-ordinary'],
                'supplier_keywords' => ['Guardian', 'Watsons'],
                'name' => 'Serum The Ordinary Niacinamide 10% + Zinc 1%',
                'slug' => 'serum-the-ordinary-niacinamide-10-zinc-1',
                'short_description' => 'Tinh chất hỗ trợ kiểm soát dầu và cải thiện bề mặt da.',
                'description' => 'Serum Niacinamide 10% + Zinc 1% thích hợp cho làn da có dầu thừa và bề mặt da không đồng đều.',
                'ingredient' => 'Niacinamide 10%, Zinc PCA 1%.',
                'usage' => 'Thoa vài giọt lên da sau bước làm sạch và trước kem dưỡng.',
                'skin_type' => 'Da dầu và da hỗn hợp',
                'origin' => 'Canada',
                'status' => 1,
                'is_featured' => 1,
                'view_count' => 1950,
            ],
            [
                'category_slugs' => [
                    'serum',
                    'tinh-chat',
                    'cham-soc-da-mat',
                ],
                'brand_slugs' => ['vichy'],
                'supplier_keywords' => ['L’Oréal', 'DKSH'],
                'name' => 'Tinh Chất Vichy Minéral 89',
                'slug' => 'tinh-chat-vichy-mineral-89',
                'short_description' => 'Tinh chất cấp ẩm giúp da trông căng mịn và khỏe khoắn.',
                'description' => 'Vichy Minéral 89 bổ sung độ ẩm và hỗ trợ củng cố hàng rào bảo vệ da với kết cấu gel nhẹ.',
                'ingredient' => 'Nước khoáng núi lửa Vichy và Hyaluronic Acid.',
                'usage' => 'Dùng 1 đến 2 lần nhấn cho toàn mặt vào buổi sáng và tối.',
                'skin_type' => 'Mọi loại da',
                'origin' => 'Pháp',
                'status' => 1,
                'is_featured' => 0,
                'view_count' => 860,
            ],
            [
                'category_slugs' => [
                    'kem-duong',
                    'duong-am',
                    'cham-soc-da-mat',
                ],
                'brand_slugs' => ['cerave'],
                'supplier_keywords' => ['L’Oréal', 'DKSH'],
                'name' => 'Kem Dưỡng Ẩm CeraVe Moisturizing Cream',
                'slug' => 'kem-duong-am-cerave-moisturizing-cream',
                'short_description' => 'Kem dưỡng ẩm hỗ trợ phục hồi hàng rào bảo vệ da.',
                'description' => 'CeraVe Moisturizing Cream cung cấp độ ẩm lâu dài, phù hợp cho cả da mặt và cơ thể.',
                'ingredient' => 'Ba loại Ceramide thiết yếu, Hyaluronic Acid và Glycerin.',
                'usage' => 'Thoa một lượng phù hợp lên vùng da cần dưỡng ẩm.',
                'skin_type' => 'Da thường đến da khô',
                'origin' => 'Hoa Kỳ',
                'status' => 1,
                'is_featured' => 1,
                'view_count' => 1480,
            ],
            [
                'category_slugs' => [
                    'kem-duong',
                    'duong-am',
                    'cham-soc-da-mat',
                ],
                'brand_slugs' => ['simple'],
                'supplier_keywords' => ['Unilever', 'Guardian'],
                'name' => 'Kem Dưỡng Simple Hydrating Light Moisturiser',
                'slug' => 'kem-duong-simple-hydrating-light-moisturiser',
                'short_description' => 'Kem dưỡng ẩm nhẹ mặt dành cho làn da nhạy cảm.',
                'description' => 'Simple Hydrating Light Moisturiser có kết cấu mỏng nhẹ, hỗ trợ duy trì độ ẩm mà không gây cảm giác nặng mặt.',
                'ingredient' => 'Vitamin B5, Vitamin E, Glycerin và Bisabolol.',
                'usage' => 'Thoa đều lên mặt và cổ sau bước serum.',
                'skin_type' => 'Da nhạy cảm và da thường',
                'origin' => 'Anh',
                'status' => 1,
                'is_featured' => 0,
                'view_count' => 720,
            ],
            [
                'category_slugs' => [
                    'mat-na',
                    'mat-na-ngu',
                    'cham-soc-da-mat',
                ],
                'brand_slugs' => ['laneige'],
                'supplier_keywords' => ['Hàn Quốc', 'K-Beauty'],
                'name' => 'Mặt Nạ Ngủ Laneige Water Sleeping Mask',
                'slug' => 'mat-na-ngu-laneige-water-sleeping-mask',
                'short_description' => 'Mặt nạ ngủ hỗ trợ cấp ẩm và làm mềm da qua đêm.',
                'description' => 'Laneige Water Sleeping Mask có kết cấu gel mát nhẹ, giúp bổ sung độ ẩm trong thời gian ngủ.',
                'ingredient' => 'Squalane, Beta-Glucan và phức hợp dưỡng ẩm.',
                'usage' => 'Thoa một lớp mỏng vào bước cuối của chu trình dưỡng da buổi tối.',
                'skin_type' => 'Mọi loại da',
                'origin' => 'Hàn Quốc',
                'status' => 1,
                'is_featured' => 1,
                'view_count' => 1320,
            ],
            [
                'category_slugs' => [
                    'toner',
                    'nuoc-hoa-hong',
                    'cham-soc-da-mat',
                ],
                'brand_slugs' => ['some-by-mi'],
                'supplier_keywords' => ['Hàn Quốc', 'K-Beauty'],
                'name' => 'Nước Hoa Hồng Some By Mi AHA BHA PHA 30 Days Miracle',
                'slug' => 'nuoc-hoa-hong-some-by-mi-aha-bha-pha',
                'short_description' => 'Toner hỗ trợ làm sạch bề mặt và chăm sóc da có khuyết điểm.',
                'description' => 'Some By Mi AHA BHA PHA Toner hỗ trợ loại bỏ tế bào chết nhẹ nhàng và làm thông thoáng bề mặt da.',
                'ingredient' => 'AHA, BHA, PHA, Tea Tree và Niacinamide.',
                'usage' => 'Thấm toner vào bông hoặc lòng bàn tay rồi vỗ nhẹ lên da.',
                'skin_type' => 'Da dầu và da có khuyết điểm',
                'origin' => 'Hàn Quốc',
                'status' => 1,
                'is_featured' => 0,
                'view_count' => 940,
            ],
            [
                'category_slugs' => [
                    'tay-da-chet',
                    'cham-soc-da-mat',
                    'skincare',
                ],
                'brand_slugs' => ['cocoon'],
                'supplier_keywords' => ['Cocoon'],
                'name' => 'Tẩy Tế Bào Chết Cocoon Cà Phê Đắk Lắk',
                'slug' => 'tay-te-bao-chet-cocoon-ca-phe-dak-lak',
                'short_description' => 'Sản phẩm tẩy tế bào chết từ cà phê giúp da mềm mịn.',
                'description' => 'Cocoon Cà Phê Đắk Lắk sử dụng hạt cà phê xay giúp loại bỏ lớp tế bào chết trên bề mặt da.',
                'ingredient' => 'Cà phê Đắk Lắk, bơ ca cao và dầu dưỡng thực vật.',
                'usage' => 'Massage nhẹ nhàng trên da ẩm rồi rửa sạch với nước.',
                'skin_type' => 'Mọi loại da',
                'origin' => 'Việt Nam',
                'status' => 1,
                'is_featured' => 1,
                'view_count' => 1180,
            ],
            [
                'category_slugs' => [
                    'duong-the',
                    'cham-soc-co-the',
                    'body-care',
                ],
                'brand_slugs' => ['vaseline'],
                'supplier_keywords' => ['Unilever'],
                'name' => 'Sữa Dưỡng Thể Vaseline Healthy Bright',
                'slug' => 'sua-duong-the-vaseline-healthy-bright',
                'short_description' => 'Sữa dưỡng thể cấp ẩm và hỗ trợ làn da trông sáng khỏe.',
                'description' => 'Vaseline Healthy Bright giúp dưỡng ẩm toàn thân với kết cấu dễ tán và nhanh thấm.',
                'ingredient' => 'Vaseline Jelly, Niacinamide và Glycerin.',
                'usage' => 'Thoa đều lên cơ thể sau khi tắm, sử dụng hằng ngày.',
                'skin_type' => 'Mọi loại da cơ thể',
                'origin' => 'Hoa Kỳ',
                'status' => 1,
                'is_featured' => 0,
                'view_count' => 760,
            ],
            [
                'category_slugs' => [
                    'duong-the',
                    'cham-soc-co-the',
                    'body-care',
                ],
                'brand_slugs' => ['nivea'],
                'supplier_keywords' => ['Beiersdorf'],
                'name' => 'Sữa Dưỡng Thể Nivea Extra Bright',
                'slug' => 'sua-duong-the-nivea-extra-bright',
                'short_description' => 'Sữa dưỡng thể giúp cấp ẩm và chăm sóc da khô.',
                'description' => 'Nivea Extra Bright có kết cấu mềm mịn, hỗ trợ giữ ẩm và cải thiện cảm giác khô ráp.',
                'ingredient' => 'Vitamin C, Vitamin E và Glycerin.',
                'usage' => 'Thoa lên toàn thân và massage nhẹ cho sản phẩm thấm đều.',
                'skin_type' => 'Da thường và da khô',
                'origin' => 'Đức',
                'status' => 1,
                'is_featured' => 0,
                'view_count' => 690,
            ],
            [
                'category_slugs' => [
                    'son-moi',
                    'trang-diem-moi',
                    'makeup',
                ],
                'brand_slugs' => ['maybelline-new-york'],
                'supplier_keywords' => ['L’Oréal'],
                'name' => 'Son Kem Maybelline SuperStay Matte Ink',
                'slug' => 'son-kem-maybelline-superstay-matte-ink',
                'short_description' => 'Son kem lì với màu sắc rõ nét và độ bám màu tốt.',
                'description' => 'Maybelline SuperStay Matte Ink mang lại lớp son lì, màu sắc nổi bật và phù hợp trang điểm hằng ngày.',
                'ingredient' => 'Color Pigments, Silicone và các thành phần tạo màng.',
                'usage' => 'Dùng đầu cọ thoa đều từ giữa môi ra hai bên.',
                'skin_type' => 'Mọi loại môi',
                'origin' => 'Hoa Kỳ',
                'status' => 1,
                'is_featured' => 1,
                'view_count' => 1740,
            ],
            [
                'category_slugs' => [
                    'kem-nen',
                    'trang-diem-mat',
                    'makeup',
                ],
                'brand_slugs' => ['loreal-paris'],
                'supplier_keywords' => ['L’Oréal'],
                'name' => 'Kem Nền L’Oréal Paris Infallible 24H Fresh Wear',
                'slug' => 'kem-nen-loreal-paris-infallible-24h-fresh-wear',
                'short_description' => 'Kem nền mỏng nhẹ giúp tạo lớp nền tự nhiên.',
                'description' => 'L’Oréal Paris Infallible Fresh Wear có độ che phủ tốt, hỗ trợ duy trì lớp nền trong thời gian dài.',
                'ingredient' => 'Color Pigments, Hyaluronic Acid và chất tạo màng.',
                'usage' => 'Lấy lượng nhỏ, tán đều bằng mút hoặc cọ trang điểm.',
                'skin_type' => 'Da thường, da hỗn hợp và da dầu',
                'origin' => 'Pháp',
                'status' => 1,
                'is_featured' => 1,
                'view_count' => 1430,
            ],
            [
                'category_slugs' => [
                    'mascara',
                    'trang-diem-mat',
                    'makeup',
                ],
                'brand_slugs' => ['maybelline-new-york'],
                'supplier_keywords' => ['L’Oréal'],
                'name' => 'Mascara Maybelline Lash Sensational',
                'slug' => 'mascara-maybelline-lash-sensational',
                'short_description' => 'Mascara giúp hàng mi trông dày và cong hơn.',
                'description' => 'Maybelline Lash Sensational có đầu cọ nhiều tầng giúp chải đều từng sợi mi và hạn chế vón cục.',
                'ingredient' => 'Sáp tổng hợp, Color Pigments và Polymer.',
                'usage' => 'Chải mascara từ chân mi lên ngọn theo chuyển động zigzag.',
                'skin_type' => 'Mọi đối tượng',
                'origin' => 'Hoa Kỳ',
                'status' => 1,
                'is_featured' => 0,
                'view_count' => 810,
            ],
            [
                'category_slugs' => [
                    'tri-mun',
                    'serum',
                    'cham-soc-da-mat',
                ],
                'brand_slugs' => ['cosrx'],
                'supplier_keywords' => ['Hàn Quốc', 'K-Beauty'],
                'name' => 'Tinh Chất COSRX Advanced Snail 96 Mucin Power Essence',
                'slug' => 'tinh-chat-cosrx-advanced-snail-96-mucin',
                'short_description' => 'Tinh chất dịch ốc sên hỗ trợ cấp ẩm và làm dịu da.',
                'description' => 'COSRX Advanced Snail 96 Mucin Power Essence có kết cấu đặc nhẹ, giúp dưỡng ẩm và hỗ trợ chăm sóc da tổn thương.',
                'ingredient' => 'Snail Secretion Filtrate 96%, Betaine và Hyaluronic Acid.',
                'usage' => 'Thoa lượng vừa đủ sau toner và vỗ nhẹ cho sản phẩm thẩm thấu.',
                'skin_type' => 'Da khô, da nhạy cảm và da có khuyết điểm',
                'origin' => 'Hàn Quốc',
                'status' => 1,
                'is_featured' => 1,
                'view_count' => 1560,
            ],
            [
                'category_slugs' => [
                    'sua-rua-mat',
                    'cham-soc-da-mat',
                    'skincare',
                ],
                'brand_slugs' => ['hada-labo'],
                'supplier_keywords' => ['Rohto', 'Nhật Bản'],
                'name' => 'Sữa Rửa Mặt Hada Labo Advanced Nourish',
                'slug' => 'sua-rua-mat-hada-labo-advanced-nourish',
                'short_description' => 'Sữa rửa mặt dịu nhẹ giúp làm sạch và duy trì độ ẩm.',
                'description' => 'Hada Labo Advanced Nourish làm sạch bụi bẩn trên da mà vẫn hạn chế cảm giác khô căng sau khi rửa.',
                'ingredient' => 'Hyaluronic Acid, Glycerin và chất làm sạch dịu nhẹ.',
                'usage' => 'Tạo bọt, massage trên da ướt rồi rửa sạch bằng nước.',
                'skin_type' => 'Da thường đến da khô',
                'origin' => 'Nhật Bản',
                'status' => 1,
                'is_featured' => 0,
                'view_count' => 670,
            ],
            [
                'category_slugs' => [
                    'mat-na',
                    'cham-soc-da-mat',
                    'skincare',
                ],
                'brand_slugs' => ['innisfree'],
                'supplier_keywords' => ['Hàn Quốc', 'K-Beauty'],
                'name' => 'Mặt Nạ Đất Sét Innisfree Super Volcanic Pore Clay Mask',
                'slug' => 'mat-na-dat-set-innisfree-super-volcanic-pore-clay-mask',
                'short_description' => 'Mặt nạ đất sét giúp làm sạch dầu thừa trên bề mặt da.',
                'description' => 'Innisfree Super Volcanic Pore Clay Mask hỗ trợ làm sạch lỗ chân lông và mang lại cảm giác thông thoáng.',
                'ingredient' => 'Tro núi lửa Jeju, Kaolin và Bentonite.',
                'usage' => 'Thoa lên da sạch, tránh mắt và môi, để khoảng 10 phút rồi rửa sạch.',
                'skin_type' => 'Da dầu và da hỗn hợp',
                'origin' => 'Hàn Quốc',
                'status' => 1,
                'is_featured' => 1,
                'view_count' => 1210,
            ],
        ];

        foreach ($products as $product) {
            $categoryId = $getCategoryId($product['category_slugs']);
            $brandId = $getBrandId($product['brand_slugs']);
            $supplierId = $getSupplierId(
                $product['supplier_keywords']
            );

            unset(
                $product['category_slugs'],
                $product['brand_slugs'],
                $product['supplier_keywords']
            );

            DB::table('products')->updateOrInsert(
                [
                    'slug' => $product['slug'],
                ],
                array_merge($product, [
                    'category_id' => $categoryId,
                    'brand_id' => $brandId,
                    'supplier_id' => $supplierId,
                    'created_by' => $createdBy,
                    'updated_by' => $createdBy,
                    'updated_at' => now(),
                    'created_at' => now(),
                    'deleted_at' => null,
                ])
            );
        }
    }
}
