<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('order_status_histories')) {
            return;
        }

        Schema::table('order_status_histories', function (Blueprint $table): void {
            /*
             * Các cột order_id, from_status, to_status, note, source và
             * created_by đã có ngay từ migration tạo bảng. Migration này
             * chỉ bổ sung thời điểm nghiệp vụ của lịch sử trạng thái.
             */
            if (! Schema::hasColumn('order_status_histories', 'occurred_at')) {
                $table->timestamp('occurred_at')
                    ->nullable()
                    ->after('source');

                $table->index(
                    ['order_id', 'occurred_at'],
                    'order_status_history_order_time_idx'
                );
            }

            /*
             * Index này hỗ trợ lọc timeline theo trạng thái. Tên index riêng
             * giúp rollback chính xác và không trùng index của migration gốc.
             */
            $table->index(
                ['order_id', 'to_status'],
                'order_status_history_order_status_idx'
            );
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('order_status_histories')) {
            return;
        }

        Schema::table('order_status_histories', function (Blueprint $table): void {
            $table->dropIndex('order_status_history_order_status_idx');

            if (Schema::hasColumn('order_status_histories', 'occurred_at')) {
                $table->dropIndex('order_status_history_order_time_idx');
                $table->dropColumn('occurred_at');
            }
        });
    }
};
