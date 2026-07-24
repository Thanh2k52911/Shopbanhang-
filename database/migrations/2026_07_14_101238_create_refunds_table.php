<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();

            // Mã hoàn tiền nội bộ
            $table->string('refund_code', 50)->unique();

            $table->foreignId('return_request_id')
                ->nullable()
                ->constrained('return_requests')
                ->nullOnDelete();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            $table->foreignId('payment_id')
                ->nullable()
                ->constrained('payments')
                ->nullOnDelete();

            $table->decimal('amount', 15, 2);

            /*
             * original_payment: hoàn về phương thức ban đầu
             * bank_transfer: chuyển khoản
             * cash: tiền mặt
             * store_credit: số dư cửa hàng
             * coupon: cấp coupon thay thế
             */
            $table->string('method', 30);

            /*
             * pending: chờ xử lý
             * processing: đang xử lý
             * completed: hoàn thành
             * failed: thất bại
             * cancelled: đã hủy
             */
            $table->string('status', 30)->default('pending');

            // Mã giao dịch hoàn tiền từ cổng thanh toán
            $table->string('provider_transaction_id', 255)
                ->nullable()
                ->index();

            $table->string('bank_name', 150)->nullable();
            $table->string('bank_account_number', 100)->nullable();
            $table->string('bank_account_name', 150)->nullable();

            $table->text('reason')->nullable();
            $table->text('admin_note')->nullable();
            $table->text('failure_reason')->nullable();

            $table->foreignId('processed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('processed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->timestamps();

            $table->index(['order_id', 'status']);
            $table->index(['return_request_id', 'status']);
            $table->index(['payment_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
