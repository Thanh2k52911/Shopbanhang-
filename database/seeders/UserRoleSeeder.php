<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserRoleSeeder extends Seeder
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
             * Kiểm tra các role bắt buộc đã tồn tại.
             */
            $requiredRoles = [
                'super_admin',
                'admin',
                'staff',
                'warehouse_staff',
                'customer_support',
                'customer',
            ];

            foreach ($requiredRoles as $roleName) {
                if (! isset($roles[$roleName])) {
                    throw new \RuntimeException(
                        "Không tìm thấy role: {$roleName}. Hãy chạy RoleSeeder trước."
                    );
                }
            }

            /*
             * Super Admin
             */
            $this->assignRole(
                'superadmin@cosmeticshop.vn',
                $roles['super_admin']
            );

            /*
             * Admin
             */
            $this->assignRole(
                'admin1@cosmeticshop.vn',
                $roles['admin']
            );

            $this->assignRole(
                'admin2@cosmeticshop.vn',
                $roles['admin']
            );

            /*
             * Staff
             */
            $staffEmails = [
                'staff1@cosmeticshop.vn',
                'staff2@cosmeticshop.vn',
                'staff3@cosmeticshop.vn',
            ];

            foreach ($staffEmails as $email) {
                $this->assignRole($email, $roles['staff']);
            }

            /*
             * Warehouse Staff
             */
            $warehouseEmails = [
                'warehouse1@cosmeticshop.vn',
                'warehouse2@cosmeticshop.vn',
            ];

            foreach ($warehouseEmails as $email) {
                $this->assignRole(
                    $email,
                    $roles['warehouse_staff']
                );
            }

            /*
             * Customer Support
             */
            $supportEmails = [
                'support1@cosmeticshop.vn',
                'support2@cosmeticshop.vn',
            ];

            foreach ($supportEmails as $email) {
                $this->assignRole(
                    $email,
                    $roles['customer_support']
                );
            }

            /*
             * Customer
             */
            $customerEmails = [];

            for ($i = 1; $i <= 20; $i++) {
                $customerEmails[] = "customer{$i}@gmail.com";
            }

            foreach ($customerEmails as $email) {
                $this->assignRole(
                    $email,
                    $roles['customer']
                );
            }
        });
    }

    private function assignRole(string $email, Role $role): void
    {
        $user = User::query()
            ->where('email', $email)
            ->first();

        if (! $user) {
            throw new \RuntimeException(
                "Không tìm thấy user có email: {$email}. Hãy chạy UserSeeder trước."
            );
        }

        /*
         * syncWithoutDetaching giúp Seeder có thể chạy lại
         * mà không tạo dữ liệu trùng trong user_roles.
         */
        $user->roles()->syncWithoutDetaching([
            $role->id,
        ]);
    }
}
