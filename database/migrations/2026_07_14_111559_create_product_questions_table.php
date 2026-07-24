<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_questions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Dành cho khách chưa đăng nhập
            $table->string('guest_name', 150)->nullable();
            $table->string('guest_email')->nullable();

            $table->text('question');

            /*
             * pending: chờ duyệt
             * published: đã hiển thị
             * answered: đã trả lời
             * hidden: đã ẩn
             * rejected: từ chối
             */
            $table->string('status', 30)->default('pending');

            $table->boolean('is_public')->default(true);

            $table->timestamp('answered_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(
                ['product_id', 'status', 'created_at'],
                'product_questions_product_status_idx'
            );

            $table->index(
                ['user_id', 'created_at'],
                'product_questions_user_time_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_questions');
    }
};
