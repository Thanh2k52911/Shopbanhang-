<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {

        DB::transaction(function () {
            $suppliers = [
                [
                    'name' => 'Công ty TNHH L’Oréal Việt Nam',
                    'contact_name' => 'Nguyễn Thanh Hương',
                    'phone' => '02473055678',
                    'email' => 'distribution@loreal-vietnam.vn',
                    'address' => 'Quận 1, Thành phố Hồ Chí Minh',
                    'tax_code' => '0303749238',
                ],
                [
                    'name' => 'Công ty TNHH Rohto-Mentholatum Việt Nam',
                    'contact_name' => 'Trần Minh Anh',
                    'phone' => '02838229322',
                    'email' => 'sales@rohto.com.vn',
                    'address' => 'Thuận An, Bình Dương',
                    'tax_code' => '3700239769',
                ],
                [
                    'name' => 'Công ty TNHH Unilever Việt Nam',
                    'contact_name' => 'Lê Ngọc Lan',
                    'phone' => '02854135686',
                    'email' => 'distribution@unilever.vn',
                    'address' => 'Quận 7, Thành phố Hồ Chí Minh',
                    'tax_code' => '0300762150',
                ],
                [
                    'name' => 'Công ty TNHH Beiersdorf Việt Nam',
                    'contact_name' => 'Phạm Minh Đức',
                    'phone' => '02854135000',
                    'email' => 'sales@beiersdorf.vn',
                    'address' => 'Thành phố Thủ Đức, Thành phố Hồ Chí Minh',
                    'tax_code' => '0310004785',
                ],
                [
                    'name' => 'Công ty TNHH DKSH Việt Nam',
                    'contact_name' => 'Nguyễn Hoàng Nam',
                    'phone' => '02839105800',
                    'email' => 'consumer.goods@dksh.vn',
                    'address' => 'Quận Tân Bình, Thành phố Hồ Chí Minh',
                    'tax_code' => '0301437034',
                ],
                [
                    'name' => 'Công ty Cổ phần Hasaki Beauty & Clinic',
                    'contact_name' => 'Trương Thanh Mai',
                    'phone' => '18006324',
                    'email' => 'supplier@hasaki.vn',
                    'address' => 'Quận 10, Thành phố Hồ Chí Minh',
                    'tax_code' => '0313612829',
                ],
                [
                    'name' => 'Công ty TNHH Guardian Việt Nam',
                    'contact_name' => 'Vũ Minh Phương',
                    'phone' => '02838273500',
                    'email' => 'business@guardian.com.vn',
                    'address' => 'Quận 1, Thành phố Hồ Chí Minh',
                    'tax_code' => '0304124749',
                ],
                [
                    'name' => 'Công ty TNHH Watsons Việt Nam',
                    'contact_name' => 'Đỗ Ngọc Linh',
                    'phone' => '02873061888',
                    'email' => 'distribution@watsons.vn',
                    'address' => 'Quận 1, Thành phố Hồ Chí Minh',
                    'tax_code' => '0314913927',
                ],
                [
                    'name' => 'Công ty TNHH Mỹ phẩm Cocoon Việt Nam',
                    'contact_name' => 'Hoàng Thùy Trang',
                    'phone' => '02838328228',
                    'email' => 'sales@cocoonvietnam.com',
                    'address' => 'Quận Bình Thạnh, Thành phố Hồ Chí Minh',
                    'tax_code' => '0313928256',
                ],
                [
                    'name' => 'Công ty Cổ phần Mỹ phẩm Quốc tế Thùy Dung',
                    'contact_name' => 'Bùi Thanh Tâm',
                    'phone' => '02432005666',
                    'email' => 'distribution@thuydung.com.vn',
                    'address' => 'Quận Cầu Giấy, Hà Nội',
                    'tax_code' => '0104487687',
                ],
                [
                    'name' => 'Nhà phân phối Mỹ phẩm Hàn Quốc K-Beauty',
                    'contact_name' => 'Nguyễn Thị Yến',
                    'phone' => '0988123456',
                    'email' => 'sales@kbeauty-distribution.vn',
                    'address' => 'Quận Nam Từ Liêm, Hà Nội',
                    'tax_code' => '0109988112',
                ],
                [
                    'name' => 'Nhà phân phối Mỹ phẩm Nhật Bản J-Beauty',
                    'contact_name' => 'Trần Quốc Minh',
                    'phone' => '0977123456',
                    'email' => 'sales@jbeauty-distribution.vn',
                    'address' => 'Quận Thanh Xuân, Hà Nội',
                    'tax_code' => '0109988223',
                ],
            ];

            foreach ($suppliers as $index => $supplierData) {
                Supplier::withTrashed()->updateOrCreate(
                    [
                        'name' => $supplierData['name'],
                    ],
                    [
                        'contact_name' => $supplierData['contact_name'],
                        'phone' => $supplierData['phone'],
                        'email' => $supplierData['email'],
                        'address' => $supplierData['address'],
                        'tax_code' => $supplierData['tax_code'],
                        'sort_order' => $index + 1,
                        'status' => 1,
                        'deleted_at' => null,
                    ]
                );
            }
        });
    }
}
