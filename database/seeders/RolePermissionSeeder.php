<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $roles = Role::query()
                ->whereIn('name', [
                    'super_admin',
                    'admin',
                    'staff',
                    'warehouse_staff',
                    'customer_support',
                    'customer',
                ])
                ->get()
                ->keyBy('name');

            /*
             * SUPER ADMIN
             * Có toàn bộ quyền trong hệ thống.
             */
            $roles['super_admin']->permissions()->sync(
                Permission::query()->pluck('id')->toArray()
            );

            /*
             * ADMIN
             * Có gần như toàn bộ quyền, ngoại trừ một số quyền nhạy cảm
             * liên quan đến phân quyền và nhật ký hệ thống.
             */
            $this->syncPermissions($roles['admin'], [
                'dashboard.view',

                'users.view',
                'users.create',
                'users.update',
                'users.delete',

                'roles.view',

                'categories.view',
                'categories.create',
                'categories.update',
                'categories.delete',

                'brands.view',
                'brands.create',
                'brands.update',
                'brands.delete',

                'suppliers.view',
                'suppliers.create',
                'suppliers.update',
                'suppliers.delete',

                'products.view',
                'products.create',
                'products.update',
                'products.delete',

                'warehouses.view',
                'warehouses.manage',

                'inventory.view',
                'inventory.import',
                'inventory.export',
                'inventory.adjust',

                'orders.view',
                'orders.confirm',
                'orders.update_status',
                'orders.cancel',
                'orders.delete',

                'payments.view',
                'payments.manage',

                'shipments.view',
                'shipments.manage',

                'coupons.view',
                'coupons.create',
                'coupons.update',
                'coupons.delete',

                'discounts.view',
                'discounts.manage',

                'reviews.view',
                'reviews.moderate',

                'questions.view',
                'questions.answer',
                'questions.moderate',

                'returns.view',
                'returns.process',

                'refunds.view',
                'refunds.process',

                'banners.manage',
                'pages.manage',
                'settings.manage',

                'contacts.view',
                'contacts.process',

                'statistics.view',
            ]);

            /*
             * STAFF
             * Nhân viên vận hành chung.
             */
            $this->syncPermissions($roles['staff'], [
                'dashboard.view',

                'categories.view',
                'brands.view',
                'suppliers.view',

                'products.view',
                'products.create',
                'products.update',

                'warehouses.view',
                'inventory.view',

                'orders.view',
                'orders.confirm',
                'orders.update_status',
                'orders.cancel',

                'payments.view',

                'shipments.view',
                'shipments.manage',

                'coupons.view',
                'discounts.view',

                'reviews.view',
                'reviews.moderate',

                'questions.view',
                'questions.answer',
                'questions.moderate',

                'returns.view',
                'returns.process',

                'refunds.view',

                'contacts.view',
                'contacts.process',

                'statistics.view',
            ]);

            /*
             * WAREHOUSE STAFF
             * Nhân viên kho.
             */
            $this->syncPermissions($roles['warehouse_staff'], [
                'dashboard.view',

                'products.view',

                'warehouses.view',
                'warehouses.manage',

                'inventory.view',
                'inventory.import',
                'inventory.export',
                'inventory.adjust',

                'orders.view',
                'orders.update_status',

                'shipments.view',
                'shipments.manage',

                'returns.view',
                'returns.process',
            ]);

            /*
             * CUSTOMER SUPPORT
             * Nhân viên chăm sóc khách hàng.
             */
            $this->syncPermissions($roles['customer_support'], [
                'dashboard.view',

                'users.view',

                'products.view',

                'orders.view',
                'orders.confirm',
                'orders.update_status',
                'orders.cancel',

                'payments.view',

                'shipments.view',

                'reviews.view',
                'reviews.moderate',

                'questions.view',
                'questions.answer',
                'questions.moderate',

                'returns.view',
                'returns.process',

                'refunds.view',

                'contacts.view',
                'contacts.process',
            ]);

            /*
             * CUSTOMER
             * Khách hàng không cần quyền truy cập trang quản trị.
             * Các chức năng mua hàng sẽ kiểm soát bằng đăng nhập,
             * policy hoặc kiểm tra chủ sở hữu dữ liệu.
             */
            $roles['customer']->permissions()->sync([]);
        });
    }

    private function syncPermissions(Role $role, array $permissionNames): void
    {
        $permissionIds = Permission::query()
            ->whereIn('name', $permissionNames)
            ->pluck('id')
            ->toArray();

        $role->permissions()->sync($permissionIds);
    }
}
