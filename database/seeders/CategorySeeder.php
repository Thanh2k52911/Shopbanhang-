<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $categories = [
                [
                    'name' => 'Chăm sóc da mặt',
                    'thumbnail' => 'categories/cham-soc-da-mat.jpg',
                    'description' => 'Các sản phẩm làm sạch, dưỡng ẩm và chăm sóc da mặt.',
                    'sort_order' => 1,
                    'children' => [
                        'Tẩy trang',
                        'Sữa rửa mặt',
                        'Toner - Nước cân bằng',
                        'Serum - Tinh chất',
                        'Kem dưỡng da',
                        'Kem chống nắng',
                        'Mặt nạ',
                        'Tẩy tế bào chết',
                        'Xịt khoáng',
                        'Chăm sóc vùng mắt',
                        'Trị mụn',
                        'Dưỡng môi',
                    ],
                ],
                [
                    'name' => 'Trang điểm',
                    'thumbnail' => 'categories/trang-diem.jpg',
                    'description' => 'Các sản phẩm trang điểm cho mặt, mắt và môi.',
                    'sort_order' => 2,
                    'children' => [
                        'Kem lót',
                        'Kem nền',
                        'Cushion',
                        'Phấn phủ',
                        'Che khuyết điểm',
                        'Má hồng',
                        'Tạo khối',
                        'Bắt sáng',
                        'Chì kẻ mày',
                        'Phấn mắt',
                        'Kẻ mắt',
                        'Mascara',
                        'Son môi',
                        'Xịt khóa nền',
                    ],
                ],
                [
                    'name' => 'Chăm sóc cơ thể',
                    'thumbnail' => 'categories/cham-soc-co-the.jpg',
                    'description' => 'Sản phẩm làm sạch, dưỡng ẩm và chăm sóc cơ thể.',
                    'sort_order' => 3,
                    'children' => [
                        'Sữa tắm',
                        'Dưỡng thể',
                        'Tẩy tế bào chết cơ thể',
                        'Kem dưỡng tay',
                        'Khử mùi',
                        'Chống nắng cơ thể',
                        'Chăm sóc vùng da nhạy cảm',
                    ],
                ],
                [
                    'name' => 'Chăm sóc tóc',
                    'thumbnail' => 'categories/cham-soc-toc.jpg',
                    'description' => 'Sản phẩm làm sạch, phục hồi và tạo kiểu tóc.',
                    'sort_order' => 4,
                    'children' => [
                        'Dầu gội',
                        'Dầu xả',
                        'Ủ tóc',
                        'Tinh dầu dưỡng tóc',
                        'Xịt dưỡng tóc',
                        'Sản phẩm tạo kiểu tóc',
                        'Thuốc nhuộm tóc',
                        'Chăm sóc da đầu',
                    ],
                ],
                [
                    'name' => 'Nước hoa',
                    'thumbnail' => 'categories/nuoc-hoa.jpg',
                    'description' => 'Nước hoa và sản phẩm tạo hương dành cho nam và nữ.',
                    'sort_order' => 5,
                    'children' => [
                        'Nước hoa nữ',
                        'Nước hoa nam',
                        'Nước hoa unisex',
                        'Xịt thơm cơ thể',
                        'Nước hoa mini',
                    ],
                ],
                [
                    'name' => 'Chăm sóc cá nhân',
                    'thumbnail' => 'categories/cham-soc-ca-nhan.jpg',
                    'description' => 'Các sản phẩm vệ sinh và chăm sóc cá nhân hằng ngày.',
                    'sort_order' => 6,
                    'children' => [
                        'Chăm sóc răng miệng',
                        'Dung dịch vệ sinh',
                        'Sản phẩm khử mùi',
                        'Dao cạo và phụ kiện',
                        'Bông tẩy trang',
                        'Khăn giấy và khăn ướt',
                    ],
                ],
                [
                    'name' => 'Dành cho nam',
                    'thumbnail' => 'categories/danh-cho-nam.jpg',
                    'description' => 'Sản phẩm chăm sóc da, tóc và cơ thể dành cho nam giới.',
                    'sort_order' => 7,
                    'children' => [
                        'Chăm sóc da nam',
                        'Chăm sóc tóc nam',
                        'Chăm sóc cơ thể nam',
                        'Cạo râu',
                        'Nước hoa nam',
                    ],
                ],
                [
                    'name' => 'Mẹ và bé',
                    'thumbnail' => 'categories/me-va-be.jpg',
                    'description' => 'Sản phẩm chăm sóc an toàn dành cho mẹ và em bé.',
                    'sort_order' => 8,
                    'children' => [
                        'Chăm sóc da cho bé',
                        'Sữa tắm và dầu gội cho bé',
                        'Chăm sóc mẹ bầu',
                        'Kem chống hăm',
                        'Dầu massage cho bé',
                    ],
                ],
                [
                    'name' => 'Dụng cụ làm đẹp',
                    'thumbnail' => 'categories/dung-cu-lam-dep.jpg',
                    'description' => 'Dụng cụ và phụ kiện hỗ trợ chăm sóc, trang điểm.',
                    'sort_order' => 9,
                    'children' => [
                        'Cọ trang điểm',
                        'Mút trang điểm',
                        'Máy rửa mặt',
                        'Máy massage da',
                        'Dụng cụ làm tóc',
                        'Gương trang điểm',
                        'Kẹp mi',
                        'Phụ kiện làm móng',
                    ],
                ],
                [
                    'name' => 'Thực phẩm làm đẹp',
                    'thumbnail' => 'categories/thuc-pham-lam-dep.jpg',
                    'description' => 'Sản phẩm bổ sung hỗ trợ làm đẹp da, tóc và vóc dáng.',
                    'sort_order' => 10,
                    'children' => [
                        'Collagen',
                        'Vitamin làm đẹp',
                        'Viên uống trắng da',
                        'Viên uống hỗ trợ tóc',
                        'Thức uống làm đẹp',
                    ],
                ],
            ];

            foreach ($categories as $categoryData) {
                $parent = Category::withTrashed()->updateOrCreate(
                    [
                        'slug' => Str::slug($categoryData['name']),
                    ],
                    [
                        'parent_id' => null,
                        'name' => $categoryData['name'],
                        'thumbnail' => $categoryData['thumbnail'],
                        'description' => $categoryData['description'],
                        'sort_order' => $categoryData['sort_order'],
                        'status' => 1,
                        'deleted_at' => null,
                    ]
                );

                foreach ($categoryData['children'] as $index => $childName) {
                    Category::withTrashed()->updateOrCreate(
                        [
                            'slug' => Str::slug($childName),
                        ],
                        [
                            'parent_id' => $parent->id,
                            'name' => $childName,
                            'thumbnail' => null,
                            'description' => "Các sản phẩm thuộc danh mục {$childName}.",
                            'sort_order' => $index + 1,
                            'status' => 1,
                            'deleted_at' => null,
                        ]
                    );
                }
            }
        });
    }
}
