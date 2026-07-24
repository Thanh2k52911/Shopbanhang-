<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();

            $table->string('code', 50)->unique();
            $table->string('name');
            $table->text('description')->nullable();

            $table->enum('discount_type', [
                'fixed',
                'percentage',
                'free_shipping',
            ]);

            $table->decimal('discount_value', 12, 2)->default(0);

            // Giảm tối đa nếu là giảm theo phần trăm
            $table->decimal('maximum_discount', 12, 2)->nullable();

            // Giá trị đơn tối thiểu
            $table->decimal('minimum_order_amount', 12, 2)->default(0);

            // Tổng lượt sử dụng toàn hệ thống
            $table->unsignedInteger('usage_limit')->nullable();

            // Số lần tối đa một người được dùng
            $table->unsignedInteger('usage_limit_per_user')->default(1);

            $table->unsignedInteger('used_count')->default(0);

            $table->boolean('first_order_only')->default(false);
            $table->boolean('is_public')->default(true);
            $table->boolean('status')->default(true);

            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'start_at', 'end_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
