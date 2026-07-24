<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'super_admin',
                'display_name' => 'Quản trị viên cao cấp',
                'description' => 'Có toàn quyền quản lý toàn bộ hệ thống.',
            ],
            [
                'name' => 'admin',
                'display_name' => 'Quản trị viên',
                'description' => 'Quản lý các hoạt động chính của cửa hàng.',
            ],
            [
                'name' => 'staff',
                'display_name' => 'Nhân viên',
                'description' => 'Xử lý sản phẩm, đơn hàng và khách hàng.',
            ],
            [
                'name' => 'warehouse_staff',
                'display_name' => 'Nhân viên kho',
                'description' => 'Quản lý kho hàng, nhập kho và xuất kho.',
            ],
            [
                'name' => 'customer_support',
                'display_name' => 'Nhân viên chăm sóc khách hàng',
                'description' => 'Hỗ trợ khách hàng, xử lý liên hệ và khiếu nại.',
            ],
            [
                'name' => 'customer',
                'display_name' => 'Khách hàng',
                'description' => 'Người dùng mua hàng trên website.',
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['name' => $role['name']],
                $role
            );
        }
    }
}
