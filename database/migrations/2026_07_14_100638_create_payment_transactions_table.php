<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('payment_id')
                ->constrained('payments')
                ->cascadeOnDelete();

            /*
             * payment: yêu cầu thanh toán
             * callback: người dùng được chuyển về website
             * webhook: cổng thanh toán gọi về hệ thống
             * refund: yêu cầu hoàn tiền
             * cancel: hủy giao dịch
             */
            $table->string('type', 30);

            $table->string('transaction_id', 255)->nullable()->index();

            $table->decimal('amount', 15, 2)->default(0);

            $table->string('status', 30);

            // Mã phản hồi từ cổng thanh toán
            $table->string('response_code', 100)->nullable();

            $table->text('message')->nullable();

            // Lưu dữ liệu gửi đi và dữ liệu nhận về
            $table->json('request_data')->nullable();
            $table->json('response_data')->nullable();

            $table->string('ip_address', 45)->nullable();

            $table->timestamp('processed_at')->nullable();

            $table->timestamps();

            $table->index(['payment_id', 'type']);
            $table->index(['payment_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
