<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('users')
            ->orderBy('id')
            ->value('id');

        $pages = [
            [
                'title' => 'Giới thiệu Cosmetic Shop',
                'slug' => 'gioi-thieu',
                'content' => '
                    <h2>Về Cosmetic Shop</h2>
                    <p>
                        Cosmetic Shop cung cấp các sản phẩm chăm sóc da,
                        trang điểm và chăm sóc cơ thể chính hãng.
                    </p>
                    <p>
                        Chúng tôi hướng tới trải nghiệm mua sắm minh bạch,
                        tiện lợi và an toàn cho khách hàng.
                    </p>
                ',
                'thumbnail' => 'pages/about-us.jpg',
                'page_type' => 'about',
                'meta_title' => 'Giới thiệu Cosmetic Shop',
                'meta_description' => 'Thông tin giới thiệu về Cosmetic Shop.',
                'meta_keywords' => 'cosmetic shop, mỹ phẩm chính hãng',
                'template' => 'pages.about',
                'show_in_header' => true,
                'show_in_footer' => true,
                'status' => true,
                'sort_order' => 1,
            ],
            [
                'title' => 'Chính sách bảo mật',
                'slug' => 'chinh-sach-bao-mat',
                'content' => '
                    <h2>Chính sách bảo mật</h2>
                    <p>
                        Cosmetic Shop cam kết bảo vệ thông tin cá nhân
                        và dữ liệu giao dịch của khách hàng.
                    </p>
                    <p>
                        Thông tin chỉ được sử dụng để xử lý đơn hàng,
                        chăm sóc khách hàng và cải thiện dịch vụ.
                    </p>
                ',
                'thumbnail' => null,
                'page_type' => 'policy',
                'meta_title' => 'Chính sách bảo mật',
                'meta_description' => 'Chính sách bảo vệ thông tin khách hàng.',
                'meta_keywords' => 'bảo mật, thông tin khách hàng',
                'template' => 'pages.policy',
                'show_in_header' => false,
                'show_in_footer' => true,
                'status' => true,
                'sort_order' => 2,
            ],
            [
                'title' => 'Chính sách đổi trả',
                'slug' => 'chinh-sach-doi-tra',
                'content' => '
                    <h2>Chính sách đổi trả</h2>
                    <p>
                        Khách hàng có thể yêu cầu đổi trả khi sản phẩm lỗi,
                        hư hỏng, giao sai hoặc không đúng mô tả.
                    </p>
                    <p>
                        Sản phẩm cần được giữ nguyên hiện trạng và có đầy đủ
                        hóa đơn hoặc thông tin đơn hàng.
                    </p>
                ',
                'thumbnail' => null,
                'page_type' => 'policy',
                'meta_title' => 'Chính sách đổi trả mỹ phẩm',
                'meta_description' => 'Quy định đổi trả tại Cosmetic Shop.',
                'meta_keywords' => 'đổi trả, hoàn tiền, mỹ phẩm',
                'template' => 'pages.policy',
                'show_in_header' => false,
                'show_in_footer' => true,
                'status' => true,
                'sort_order' => 3,
            ],
            [
                'title' => 'Chính sách vận chuyển',
                'slug' => 'chinh-sach-van-chuyen',
                'content' => '
                    <h2>Chính sách vận chuyển</h2>
                    <p>
                        Cosmetic Shop hỗ trợ giao hàng toàn quốc thông qua
                        các đối tác vận chuyển uy tín.
                    </p>
                    <p>
                        Thời gian giao hàng phụ thuộc vào địa chỉ nhận hàng
                        và phương thức vận chuyển khách lựa chọn.
                    </p>
                ',
                'thumbnail' => null,
                'page_type' => 'policy',
                'meta_title' => 'Chính sách vận chuyển',
                'meta_description' => 'Thông tin phí và thời gian vận chuyển.',
                'meta_keywords' => 'vận chuyển, giao hàng, phí ship',
                'template' => 'pages.policy',
                'show_in_header' => false,
                'show_in_footer' => true,
                'status' => true,
                'sort_order' => 4,
            ],
            [
                'title' => 'Hướng dẫn mua hàng',
                'slug' => 'huong-dan-mua-hang',
                'content' => '
                    <h2>Hướng dẫn mua hàng</h2>
                    <ol>
                        <li>Chọn sản phẩm và biến thể phù hợp.</li>
                        <li>Thêm sản phẩm vào giỏ hàng.</li>
                        <li>Nhập địa chỉ nhận hàng.</li>
                        <li>Chọn phương thức thanh toán.</li>
                        <li>Xác nhận và theo dõi đơn hàng.</li>
                    </ol>
                ',
                'thumbnail' => 'pages/shopping-guide.jpg',
                'page_type' => 'guide',
                'meta_title' => 'Hướng dẫn mua hàng',
                'meta_description' => 'Các bước đặt hàng tại Cosmetic Shop.',
                'meta_keywords' => 'hướng dẫn mua hàng, đặt hàng mỹ phẩm',
                'template' => 'pages.guide',
                'show_in_header' => true,
                'show_in_footer' => true,
                'status' => true,
                'sort_order' => 5,
            ],
        ];

        foreach ($pages as $page) {
            DB::table('pages')->updateOrInsert(
                [
                    'slug' => $page['slug'],
                ],
                array_merge($page, [
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
