<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recently_viewed_products', function (Blueprint $table) {
            $table->id();

            // Có thể null nếu khách chưa đăng nhập
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('session_id', 255)->nullable();

            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();

            $table->unsignedInteger('view_count')->default(1);

            $table->timestamp('last_viewed_at')->useCurrent();

            $table->timestamps();

            $table->index(
                ['user_id', 'last_viewed_at'],
                'viewed_products_user_time_idx'
            );

            $table->index(
                ['session_id', 'last_viewed_at'],
                'viewed_products_session_time_idx'
            );

            $table->index(
                ['product_id', 'last_viewed_at'],
                'viewed_products_product_time_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recently_viewed_products');
    }
};
