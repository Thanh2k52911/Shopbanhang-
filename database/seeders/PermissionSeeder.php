<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Dashboard
            [
                'name' => 'dashboard.view',
                'display_name' => 'Xem trang quản trị',
                'description' => 'Cho phép truy cập trang tổng quan quản trị.',
            ],

            // Người dùng
            [
                'name' => 'users.view',
                'display_name' => 'Xem người dùng',
                'description' => 'Xem danh sách và thông tin người dùng.',
            ],
            [
                'name' => 'users.create',
                'display_name' => 'Thêm người dùng',
                'description' => 'Tạo tài khoản người dùng mới.',
            ],
            [
                'name' => 'users.update',
                'display_name' => 'Cập nhật người dùng',
                'description' => 'Chỉnh sửa thông tin người dùng.',
            ],
            [
                'name' => 'users.delete',
                'display_name' => 'Xóa người dùng',
                'description' => 'Xóa tài khoản người dùng.',
            ],

            // Vai trò
            [
                'name' => 'roles.view',
                'display_name' => 'Xem vai trò',
                'description' => 'Xem danh sách vai trò.',
            ],
            [
                'name' => 'roles.create',
                'display_name' => 'Thêm vai trò',
                'description' => 'Tạo vai trò mới.',
            ],
            [
                'name' => 'roles.update',
                'display_name' => 'Cập nhật vai trò',
                'description' => 'Chỉnh sửa vai trò.',
            ],
            [
                'name' => 'roles.delete',
                'display_name' => 'Xóa vai trò',
                'description' => 'Xóa vai trò.',
            ],
            [
                'name' => 'roles.assign_permissions',
                'display_name' => 'Phân quyền cho vai trò',
                'description' => 'Gán hoặc thu hồi quyền của vai trò.',
            ],

            // Danh mục
            [
                'name' => 'categories.view',
                'display_name' => 'Xem danh mục',
                'description' => 'Xem danh sách danh mục.',
            ],
            [
                'name' => 'categories.create',
                'display_name' => 'Thêm danh mục',
                'description' => 'Tạo danh mục mới.',
            ],
            [
                'name' => 'categories.update',
                'display_name' => 'Cập nhật danh mục',
                'description' => 'Chỉnh sửa danh mục.',
            ],
            [
                'name' => 'categories.delete',
                'display_name' => 'Xóa danh mục',
                'description' => 'Xóa danh mục.',
            ],

            // Thương hiệu
            [
                'name' => 'brands.view',
                'display_name' => 'Xem thương hiệu',
                'description' => 'Xem danh sách thương hiệu.',
            ],
            [
                'name' => 'brands.create',
                'display_name' => 'Thêm thương hiệu',
                'description' => 'Tạo thương hiệu mới.',
            ],
            [
                'name' => 'brands.update',
                'display_name' => 'Cập nhật thương hiệu',
                'description' => 'Chỉnh sửa thương hiệu.',
            ],
            [
                'name' => 'brands.delete',
                'display_name' => 'Xóa thương hiệu',
                'description' => 'Xóa thương hiệu.',
            ],

            // Nhà cung cấp
            [
                'name' => 'suppliers.view',
                'display_name' => 'Xem nhà cung cấp',
                'description' => 'Xem danh sách nhà cung cấp.',
            ],
            [
                'name' => 'suppliers.create',
                'display_name' => 'Thêm nhà cung cấp',
                'description' => 'Tạo nhà cung cấp mới.',
            ],
            [
                'name' => 'suppliers.update',
                'display_name' => 'Cập nhật nhà cung cấp',
                'description' => 'Chỉnh sửa nhà cung cấp.',
            ],
            [
                'name' => 'suppliers.delete',
                'display_name' => 'Xóa nhà cung cấp',
                'description' => 'Xóa nhà cung cấp.',
            ],

            // Sản phẩm
            [
                'name' => 'products.view',
                'display_name' => 'Xem sản phẩm',
                'description' => 'Xem danh sách và chi tiết sản phẩm.',
            ],
            [
                'name' => 'products.create',
                'display_name' => 'Thêm sản phẩm',
                'description' => 'Tạo sản phẩm mới.',
            ],
            [
                'name' => 'products.update',
                'display_name' => 'Cập nhật sản phẩm',
                'description' => 'Chỉnh sửa sản phẩm.',
            ],
            [
                'name' => 'products.delete',
                'display_name' => 'Xóa sản phẩm',
                'description' => 'Xóa sản phẩm.',
            ],

            // Kho hàng
            [
                'name' => 'warehouses.view',
                'display_name' => 'Xem kho hàng',
                'description' => 'Xem thông tin kho hàng.',
            ],
            [
                'name' => 'warehouses.manage',
                'display_name' => 'Quản lý kho hàng',
                'description' => 'Thêm, sửa và quản lý kho hàng.',
            ],
            [
                'name' => 'inventory.view',
                'display_name' => 'Xem tồn kho',
                'description' => 'Xem số lượng hàng tồn kho.',
            ],
            [
                'name' => 'inventory.import',
                'display_name' => 'Nhập kho',
                'description' => 'Tạo giao dịch nhập kho.',
            ],
            [
                'name' => 'inventory.export',
                'display_name' => 'Xuất kho',
                'description' => 'Tạo giao dịch xuất kho.',
            ],
            [
                'name' => 'inventory.adjust',
                'display_name' => 'Điều chỉnh kho',
                'description' => 'Điều chỉnh số lượng hàng trong kho.',
            ],

            // Đơn hàng
            [
                'name' => 'orders.view',
                'display_name' => 'Xem đơn hàng',
                'description' => 'Xem danh sách và chi tiết đơn hàng.',
            ],
            [
                'name' => 'orders.confirm',
                'display_name' => 'Xác nhận đơn hàng',
                'description' => 'Xác nhận đơn hàng của khách.',
            ],
            [
                'name' => 'orders.update_status',
                'display_name' => 'Cập nhật trạng thái đơn hàng',
                'description' => 'Thay đổi trạng thái xử lý đơn hàng.',
            ],
            [
                'name' => 'orders.cancel',
                'display_name' => 'Hủy đơn hàng',
                'description' => 'Hủy đơn hàng.',
            ],
            [
                'name' => 'orders.delete',
                'display_name' => 'Xóa đơn hàng',
                'description' => 'Xóa đơn hàng khỏi hệ thống.',
            ],

            // Thanh toán
            [
                'name' => 'payments.view',
                'display_name' => 'Xem thanh toán',
                'description' => 'Xem thông tin thanh toán.',
            ],
            [
                'name' => 'payments.manage',
                'display_name' => 'Quản lý thanh toán',
                'description' => 'Cập nhật và xử lý thanh toán.',
            ],

            // Vận chuyển
            [
                'name' => 'shipments.view',
                'display_name' => 'Xem vận chuyển',
                'description' => 'Xem thông tin vận chuyển.',
            ],
            [
                'name' => 'shipments.manage',
                'display_name' => 'Quản lý vận chuyển',
                'description' => 'Tạo và cập nhật kiện hàng.',
            ],

            // Coupon
            [
                'name' => 'coupons.view',
                'display_name' => 'Xem mã giảm giá',
                'description' => 'Xem danh sách mã giảm giá.',
            ],
            [
                'name' => 'coupons.create',
                'display_name' => 'Thêm mã giảm giá',
                'description' => 'Tạo mã giảm giá mới.',
            ],
            [
                'name' => 'coupons.update',
                'display_name' => 'Cập nhật mã giảm giá',
                'description' => 'Chỉnh sửa mã giảm giá.',
            ],
            [
                'name' => 'coupons.delete',
                'display_name' => 'Xóa mã giảm giá',
                'description' => 'Xóa mã giảm giá.',
            ],

            // Khuyến mãi
            [
                'name' => 'discounts.view',
                'display_name' => 'Xem chương trình khuyến mãi',
                'description' => 'Xem danh sách chương trình khuyến mãi.',
            ],
            [
                'name' => 'discounts.manage',
                'display_name' => 'Quản lý chương trình khuyến mãi',
                'description' => 'Tạo và chỉnh sửa chương trình khuyến mãi.',
            ],

            // Đánh giá
            [
                'name' => 'reviews.view',
                'display_name' => 'Xem đánh giá',
                'description' => 'Xem đánh giá sản phẩm.',
            ],
            [
                'name' => 'reviews.moderate',
                'display_name' => 'Kiểm duyệt đánh giá',
                'description' => 'Duyệt, ẩn và phản hồi đánh giá.',
            ],

            // Hỏi đáp sản phẩm
            [
                'name' => 'questions.view',
                'display_name' => 'Xem câu hỏi sản phẩm',
                'description' => 'Xem câu hỏi của khách hàng.',
            ],
            [
                'name' => 'questions.answer',
                'display_name' => 'Trả lời câu hỏi sản phẩm',
                'description' => 'Trả lời câu hỏi của khách hàng.',
            ],
            [
                'name' => 'questions.moderate',
                'display_name' => 'Kiểm duyệt câu hỏi sản phẩm',
                'description' => 'Duyệt hoặc ẩn câu hỏi sản phẩm.',
            ],

            // Đổi trả và hoàn tiền
            [
                'name' => 'returns.view',
                'display_name' => 'Xem yêu cầu đổi trả',
                'description' => 'Xem yêu cầu đổi trả của khách hàng.',
            ],
            [
                'name' => 'returns.process',
                'display_name' => 'Xử lý yêu cầu đổi trả',
                'description' => 'Phê duyệt hoặc từ chối yêu cầu đổi trả.',
            ],
            [
                'name' => 'refunds.view',
                'display_name' => 'Xem hoàn tiền',
                'description' => 'Xem thông tin hoàn tiền.',
            ],
            [
                'name' => 'refunds.process',
                'display_name' => 'Xử lý hoàn tiền',
                'description' => 'Thực hiện và cập nhật hoàn tiền.',
            ],

            // CMS
            [
                'name' => 'banners.manage',
                'display_name' => 'Quản lý banner',
                'description' => 'Quản lý banner trên website.',
            ],
            [
                'name' => 'pages.manage',
                'display_name' => 'Quản lý trang nội dung',
                'description' => 'Quản lý các trang nội dung và chính sách.',
            ],
            [
                'name' => 'settings.manage',
                'display_name' => 'Quản lý cấu hình',
                'description' => 'Quản lý cấu hình website.',
            ],

            // Chăm sóc khách hàng
            [
                'name' => 'contacts.view',
                'display_name' => 'Xem liên hệ',
                'description' => 'Xem nội dung khách hàng liên hệ.',
            ],
            [
                'name' => 'contacts.process',
                'display_name' => 'Xử lý liên hệ',
                'description' => 'Phản hồi và xử lý liên hệ khách hàng.',
            ],

            // Báo cáo
            [
                'name' => 'statistics.view',
                'display_name' => 'Xem thống kê',
                'description' => 'Xem thống kê bán hàng và sản phẩm.',
            ],
            [
                'name' => 'audit_logs.view',
                'display_name' => 'Xem nhật ký hệ thống',
                'description' => 'Xem lịch sử thao tác trong hệ thống.',
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }
    }
}
