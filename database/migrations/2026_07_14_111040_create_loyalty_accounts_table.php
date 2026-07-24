<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loyalty_accounts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('tier_id')
                ->nullable()
                ->constrained('loyalty_tiers')
                ->nullOnDelete();

            // Điểm hiện có thể sử dụng
            $table->unsignedBigInteger('available_points')->default(0);

            // Điểm đang chờ xác nhận sau khi đặt hàng
            $table->unsignedBigInteger('pending_points')->default(0);

            // Tổng điểm từng kiếm được
            $table->unsignedBigInteger('lifetime_earned_points')->default(0);

            // Tổng điểm từng sử dụng
            $table->unsignedBigInteger('lifetime_redeemed_points')->default(0);

            // Tổng chi tiêu để xét hạng
            $table->decimal('lifetime_spending', 15, 2)->default(0);

            $table->timestamp('tier_started_at')->nullable();
            $table->timestamp('tier_expires_at')->nullable();

            $table->timestamps();

            // Mỗi user chỉ có một tài khoản điểm
            $table->unique('user_id');

            $table->index('tier_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_accounts');
    }
};
