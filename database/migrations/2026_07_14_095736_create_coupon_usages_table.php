<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupon_usages', function (Blueprint $table) {
            $table->id();

            $table->foreignId('coupon_id')
                ->constrained('coupons')
                ->cascadeOnDelete();

            // Có thể null nếu khách chưa đăng nhập
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            // Số tiền thực tế được giảm
            $table->decimal('discount_amount', 15, 2)->default(0);

            $table->timestamp('used_at')->useCurrent();

            $table->timestamps();

            // Một coupon không được ghi nhận hai lần trong cùng đơn
            $table->unique(['coupon_id', 'order_id']);

            $table->index(['coupon_id', 'user_id']);
            $table->index(['user_id', 'used_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupon_usages');
    }
};
