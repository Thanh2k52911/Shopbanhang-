<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderAddressSeeder extends Seeder
{
    public function run(): void
    {
        $addresses = [
            'ORD-DEMO-0001' => [
                'receiver_name' => 'Nguyễn Văn An',
                'phone' => '0901000001',
                'email' => 'an@example.com',
                'province' => 'Hà Nội',
                'district' => 'Hai Bà Trưng',
                'ward' => 'Bách Khoa',
                'address' => 'Số 1 Đại Cồ Việt',
                'note' => 'Gọi trước khi giao.',
            ],
            'ORD-DEMO-0002' => [
                'receiver_name' => 'Trần Thị Bình',
                'phone' => '0901000002',
                'email' => 'binh@example.com',
                'province' => 'Hà Nội',
                'district' => 'Cầu Giấy',
                'ward' => 'Dịch Vọng',
                'address' => 'Số 25 Trần Thái Tông',
                'note' => null,
            ],
            'ORD-DEMO-0003' => [
                'receiver_name' => 'Lê Minh Châu',
                'phone' => '0901000003',
                'email' => 'chau@example.com',
                'province' => 'Hà Nội',
                'district' => 'Nam Từ Liêm',
                'ward' => 'Mỹ Đình 2',
                'address' => 'Số 12 Nguyễn Hoàng',
                'note' => 'Giao sau 18 giờ.',
            ],
            'ORD-DEMO-0004' => [
                'receiver_name' => 'Phạm Hoàng Dũng',
                'phone' => '0901000004',
                'email' => 'dung@example.com',
                'province' => 'Hà Nội',
                'district' => 'Thanh Xuân',
                'ward' => 'Nhân Chính',
                'address' => 'Số 90 Nguyễn Tuân',
                'note' => null,
            ],
            'ORD-DEMO-0005' => [
                'receiver_name' => 'Nguyễn Thu Hà',
                'phone' => '0901000005',
                'email' => 'ha@example.com',
                'province' => 'Hà Nội',
                'district' => 'Hoàng Mai',
                'ward' => 'Định Công',
                'address' => 'Số 18 Định Công Thượng',
                'note' => null,
            ],
        ];

        foreach ($addresses as $orderCode => $address) {
            $orderId = DB::table('orders')
                ->where('order_code', $orderCode)
                ->value('id');

            if (!$orderId) {
                $this->command?->warn(
                    "Không tìm thấy đơn hàng: {$orderCode}"
                );

                continue;
            }

            $fullAddress = implode(', ', [
                $address['address'],
                $address['ward'],
                $address['district'],
                $address['province'],
            ]);

            DB::table('order_addresses')->updateOrInsert(
                [
                    'order_id' => $orderId,
                    'type' => 'shipping',
                ],
                [
                    'receiver_name' => $address['receiver_name'],
                    'phone' => $address['phone'],
                    'email' => $address['email'],
                    'province' => $address['province'],
                    'district' => $address['district'],
                    'ward' => $address['ward'],
                    'address' => $address['address'],
                    'full_address' => $fullAddress,
                    'note' => $address['note'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
