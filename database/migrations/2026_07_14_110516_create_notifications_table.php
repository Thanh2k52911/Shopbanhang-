<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Class thông báo của Laravel
            $table->string('type');

            /*
             * Có thể gửi thông báo cho User,
             * Admin hoặc bất kỳ Model nào.
             */
            $table->nullableMorphs('notifiable');

            $table->string('title', 255)->nullable();

            $table->text('message')->nullable();

            /*
             * order: thông báo đơn hàng
             * payment: thanh toán
             * shipping: vận chuyển
             * promotion: khuyến mãi
             * system: hệ thống
             * review: đánh giá
             */
            $table->string('category', 50)->default('system');

            // Link khi người dùng bấm thông báo
            $table->string('action_url', 1000)->nullable();

            // Icon hoặc ảnh thông báo
            $table->string('image', 500)->nullable();

            /*
             * low, normal, high, urgent
             */
            $table->string('priority', 20)->default('normal');

            // Dữ liệu mở rộng
            $table->json('data')->nullable();

            $table->timestamp('read_at')->nullable();

            $table->timestamps();

            $table->index(
                ['notifiable_type', 'notifiable_id', 'read_at'],
                'notifications_notifiable_read_idx'
            );

            $table->index(
                ['category', 'created_at'],
                'notifications_category_created_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
