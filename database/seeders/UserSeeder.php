<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            // Super Admin
            [
                'name' => 'Quản trị hệ thống',
                'email' => 'superadmin@cosmeticshop.vn',
                'password' => '12345678',
            ],

            // Admin
            [
                'name' => 'Nguyễn Minh Quản',
                'email' => 'admin1@cosmeticshop.vn',
                'password' => '12345678',
            ],
            [
                'name' => 'Trần Thu Hà',
                'email' => 'admin2@cosmeticshop.vn',
                'password' => '12345678',
            ],

            // Nhân viên chung
            [
                'name' => 'Lê Minh Đức',
                'email' => 'staff1@cosmeticshop.vn',
                'password' => '12345678',
            ],
            [
                'name' => 'Phạm Ngọc Anh',
                'email' => 'staff2@cosmeticshop.vn',
                'password' => '12345678',
            ],
            [
                'name' => 'Hoàng Thị Mai',
                'email' => 'staff3@cosmeticshop.vn',
                'password' => '12345678',
            ],

            // Nhân viên kho
            [
                'name' => 'Nguyễn Văn Kho',
                'email' => 'warehouse1@cosmeticshop.vn',
                'password' => '12345678',
            ],
            [
                'name' => 'Đỗ Thành Nam',
                'email' => 'warehouse2@cosmeticshop.vn',
                'password' => '12345678',
            ],

            // Chăm sóc khách hàng
            [
                'name' => 'Vũ Thanh Hương',
                'email' => 'support1@cosmeticshop.vn',
                'password' => '12345678',
            ],
            [
                'name' => 'Bùi Ngọc Linh',
                'email' => 'support2@cosmeticshop.vn',
                'password' => '12345678',
            ],

            // Khách hàng
            [
                'name' => 'Nguyễn Hải Yến',
                'email' => 'customer1@gmail.com',
                'password' => '12345678',
            ],
            [
                'name' => 'Trần Khánh Linh',
                'email' => 'customer2@gmail.com',
                'password' => '12345678',
            ],
            [
                'name' => 'Lê Thảo My',
                'email' => 'customer3@gmail.com',
                'password' => '12345678',
            ],
            [
                'name' => 'Phạm Minh Anh',
                'email' => 'customer4@gmail.com',
                'password' => '12345678',
            ],
            [
                'name' => 'Hoàng Ngọc Mai',
                'email' => 'customer5@gmail.com',
                'password' => '12345678',
            ],
            [
                'name' => 'Đặng Thu Trang',
                'email' => 'customer6@gmail.com',
                'password' => '12345678',
            ],
            [
                'name' => 'Bùi Thanh Tâm',
                'email' => 'customer7@gmail.com',
                'password' => '12345678',
            ],
            [
                'name' => 'Đỗ Quỳnh Anh',
                'email' => 'customer8@gmail.com',
                'password' => '12345678',
            ],
            [
                'name' => 'Vũ Minh Châu',
                'email' => 'customer9@gmail.com',
                'password' => '12345678',
            ],
            [
                'name' => 'Ngô Phương Thảo',
                'email' => 'customer10@gmail.com',
                'password' => '12345678',
            ],
            [
                'name' => 'Nguyễn Đức Anh',
                'email' => 'customer11@gmail.com',
                'password' => '12345678',
            ],
            [
                'name' => 'Trần Quốc Bảo',
                'email' => 'customer12@gmail.com',
                'password' => '12345678',
            ],
            [
                'name' => 'Lê Hoàng Long',
                'email' => 'customer13@gmail.com',
                'password' => '12345678',
            ],
            [
                'name' => 'Phạm Tuấn Kiệt',
                'email' => 'customer14@gmail.com',
                'password' => '12345678',
            ],
            [
                'name' => 'Hoàng Minh Quân',
                'email' => 'customer15@gmail.com',
                'password' => '12345678',
            ],
            [
                'name' => 'Đặng Gia Huy',
                'email' => 'customer16@gmail.com',
                'password' => '12345678',
            ],
            [
                'name' => 'Bùi Anh Tuấn',
                'email' => 'customer17@gmail.com',
                'password' => '12345678',
            ],
            [
                'name' => 'Đỗ Minh Khang',
                'email' => 'customer18@gmail.com',
                'password' => '12345678',
            ],
            [
                'name' => 'Vũ Nhật Nam',
                'email' => 'customer19@gmail.com',
                'password' => '12345678',
            ],
            [
                'name' => 'Ngô Trung Hiếu',
                'email' => 'customer20@gmail.com',
                'password' => '12345678',
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                [
                    'email' => $userData['email'],
                ],
                [
                    'name' => $userData['name'],

                    /*
                     * Hash::make chỉ nên chạy khi tạo hoặc cập nhật
                     * dữ liệu mẫu. Không lưu mật khẩu dạng chữ thường.
                     */
                    'password' => Hash::make($userData['password']),

                    'email_verified_at' => now(),

                    'remember_token' => Str::random(10),
                ]
            );
        }
    }
}
