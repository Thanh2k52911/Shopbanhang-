<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            // Mã thanh toán nội bộ
            $table->string('payment_code', 50)->unique();

            /*
             * cod: thanh toán khi nhận hàng
             * bank_transfer: chuyển khoản ngân hàng
             * vnpay, momo, zalopay: cổng thanh toán
             */
            $table->string('method', 30);

            /*
             * pending: chờ thanh toán
             * processing: đang xử lý
             * paid: thành công
             * failed: thất bại
             * cancelled: đã hủy
             * refunded: đã hoàn toàn bộ
             * partially_refunded: hoàn một phần
             */
            $table->string('status', 30)->default('pending');

            $table->decimal('amount', 15, 2);
            $table->string('currency', 10)->default('VND');

            // Mã giao dịch do VNPay, MoMo hoặc ngân hàng trả về
            $table->string('provider_transaction_id', 255)
                ->nullable()
                ->index();

            $table->string('bank_code', 50)->nullable();
            $table->string('card_type', 50)->nullable();

            // Đường dẫn chuyển người dùng đến trang thanh toán
            $table->text('payment_url')->nullable();

            $table->text('failure_reason')->nullable();

            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->timestamps();

            $table->index(['order_id', 'status']);
            $table->index(['method', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
