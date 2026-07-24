<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();

            $table->string('contact_code', 50)->unique();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('name', 150);
            $table->string('email');
            $table->string('phone', 20)->nullable();

            /*
             * general: liên hệ chung
             * order: đơn hàng
             * product: sản phẩm
             * payment: thanh toán
             * shipping: vận chuyển
             * return: đổi trả
             * complaint: khiếu nại
             */
            $table->string('type', 30)->default('general');

            $table->string('subject', 255);
            $table->text('message');

            $table->foreignId('order_id')
                ->nullable()
                ->constrained('orders')
                ->nullOnDelete();

            /*
             * new: mới
             * processing: đang xử lý
             * replied: đã phản hồi
             * closed: đã đóng
             * spam: thư rác
             */
            $table->string('status', 30)->default('new');

            $table->string('priority', 20)->default('normal');

            $table->foreignId('assigned_to')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('admin_note')->nullable();

            $table->timestamp('replied_at')->nullable();
            $table->timestamp('closed_at')->nullable();

            $table->timestamps();

            $table->index(
                ['status', 'priority', 'created_at'],
                'contact_messages_status_priority_idx'
            );

            $table->index(
                ['assigned_to', 'status'],
                'contact_messages_assigned_status_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
    }
};
