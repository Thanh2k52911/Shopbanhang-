<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipment_status_histories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('shipment_id')
                ->constrained('shipments')
                ->cascadeOnDelete();

            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);

            $table->string('location', 255)->nullable();
            $table->text('description')->nullable();

            /*
             * system: hệ thống cập nhật
             * admin: quản trị viên cập nhật
             * provider: đơn vị giao hàng cập nhật
             */
            $table->string('source', 30)->default('system');

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Dữ liệu gốc từ GHN, GHTK...
            $table->json('provider_data')->nullable();

            // Thời gian trạng thái thực sự xảy ra
            $table->timestamp('occurred_at')->nullable();

            $table->timestamps();

            $table->index(['shipment_id', 'occurred_at']);
            $table->index(['shipment_id', 'to_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipment_status_histories');
    }
};
