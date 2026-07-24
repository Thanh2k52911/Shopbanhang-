<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loyalty_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('loyalty_account_id')
                ->constrained('loyalty_accounts')
                ->cascadeOnDelete();

            $table->foreignId('order_id')
                ->nullable()
                ->constrained('orders')
                ->nullOnDelete();

            /*
             * earn: cộng điểm
             * redeem: sử dụng điểm
             * refund: hoàn lại điểm
             * expire: điểm hết hạn
             * adjust: Admin điều chỉnh
             * cancel: hủy điểm của đơn bị hủy
             */
            $table->string('type', 30);

            /*
             * Số dương: cộng điểm
             * Số âm: trừ điểm
             */
            $table->bigInteger('points');

            $table->unsignedBigInteger('balance_before')->default(0);
            $table->unsignedBigInteger('balance_after')->default(0);

            // Giá trị tiền tương ứng với điểm
            $table->decimal('monetary_value', 15, 2)->default(0);

            /*
             * pending: chờ xác nhận
             * completed: đã áp dụng
             * cancelled: bị hủy
             * expired: hết hạn
             */
            $table->string('status', 30)->default('completed');

            $table->string('reference_type', 100)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();

            $table->text('description')->nullable();

            $table->timestamp('available_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(
                ['loyalty_account_id', 'status', 'created_at'],
                'loyalty_transactions_account_status_idx'
            );

            $table->index(
                ['reference_type', 'reference_id'],
                'loyalty_transactions_reference_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_transactions');
    }
};
