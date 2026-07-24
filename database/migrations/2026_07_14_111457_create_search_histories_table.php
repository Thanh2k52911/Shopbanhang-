<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('search_histories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('session_id', 255)->nullable();

            $table->string('keyword', 255);

            // Lưu bộ lọc: thương hiệu, giá, loại da...
            $table->json('filters')->nullable();

            $table->unsignedInteger('result_count')->default(0);

            // Người dùng có bấm vào sản phẩm nào không
            $table->foreignId('clicked_product_id')
                ->nullable()
                ->constrained('products')
                ->nullOnDelete();

            $table->string('ip_address', 45)->nullable();

            $table->timestamps();

            $table->index(
                ['user_id', 'created_at'],
                'search_histories_user_time_idx'
            );

            $table->index(
                ['session_id', 'created_at'],
                'search_histories_session_time_idx'
            );

            $table->index('keyword');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('search_histories');
    }
};
