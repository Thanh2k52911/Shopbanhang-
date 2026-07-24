<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReturnStatusHistorySeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('users')
            ->orderBy('id')
            ->value('id');

        $histories = [
            'RET-DEMO-0001' => [
                [
                    'from_status' => null,
                    'to_status' => 'pending',
                    'note' => 'Khách hàng đã gửi yêu cầu trả hàng.',
                    'source' => 'customer',
                    'created_by' => null,
                    'created_at' => now()->subDays(2),
                ],
                [
                    'from_status' => 'pending',
                    'to_status' => 'approved',
                    'note' => 'Shop đã duyệt yêu cầu trả hàng.',
                    'source' => 'admin',
                    'created_by' => $adminId,
                    'created_at' => now()->subDay(),
                ],
            ],

            'RET-DEMO-0002' => [
                [
                    'from_status' => null,
                    'to_status' => 'pending',
                    'note' => 'Khách hàng đã gửi yêu cầu hoàn tiền.',
                    'source' => 'customer',
                    'created_by' => null,
                    'created_at' => now()->subHours(12),
                ],
            ],
        ];

        foreach ($histories as $returnCode => $items) {
            $returnRequestId = DB::table('return_requests')
                ->where('return_code', $returnCode)
                ->value('id');

            if (!$returnRequestId) {
                $this->command?->warn(
                    "Không tìm thấy return request: {$returnCode}"
                );

                continue;
            }

            foreach ($items as $item) {
                $exists = DB::table('return_status_histories')
                    ->where('return_request_id', $returnRequestId)
                    ->where('to_status', $item['to_status'])
                    ->where('created_at', $item['created_at'])
                    ->exists();

                if ($exists) {
                    continue;
                }

                DB::table('return_status_histories')->insert([
                    'return_request_id' => $returnRequestId,
                    'from_status' => $item['from_status'],
                    'to_status' => $item['to_status'],
                    'note' => $item['note'],
                    'source' => $item['source'],
                    'created_by' => $item['created_by'],
                    'created_at' => $item['created_at'],
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
