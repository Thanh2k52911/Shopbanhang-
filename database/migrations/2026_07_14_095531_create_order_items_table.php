<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            /*
             * Cho phép giữ lại chi tiết đơn hàng
             * kể cả khi sản phẩm hoặc SKU đã bị xóa.
             */
            $table->foreignId('product_id')
                ->nullable()
                ->constrained('products')
                ->nullOnDelete();

            $table->foreignId('variant_id')
                ->nullable()
                ->constrained('product_variants')
                ->nullOnDelete();

            $table->foreignId('sku_id')
                ->nullable()
                ->constrained('product_skus')
                ->nullOnDelete();

            /*
             * Snapshot thông tin sản phẩm tại thời điểm mua.
             * Không lấy trực tiếp từ products khi xem đơn cũ.
             */
            $table->string('product_name');
            $table->string('product_slug')->nullable();
            $table->string('variant_name')->nullable();
            $table->string('sku_code', 100)->nullable();
            $table->string('barcode', 100)->nullable();
            $table->string('image_path')->nullable();

            // Giá niêm yết trước giảm giá
            $table->decimal('original_price', 15, 2)->default(0);

            // Giá bán thực tế của một sản phẩm
            $table->decimal('unit_price', 15, 2)->default(0);

            // Số tiền giảm trên một sản phẩm
            $table->decimal('discount_amount', 15, 2)->default(0);

            $table->unsignedInteger('quantity');

            // Thành tiền của dòng sản phẩm
            $table->decimal('total_price', 15, 2)->default(0);

            // Hỗ trợ kiểm soát đánh giá sau khi mua
            $table->boolean('is_reviewed')->default(false);

            // Số lượng đã hoàn trả
            $table->unsignedInteger('returned_quantity')->default(0);

            // Số lượng đã hoàn tiền
            $table->unsignedInteger('refunded_quantity')->default(0);

            $table->timestamps();

            $table->index(['order_id', 'product_id']);
            $table->index(['order_id', 'sku_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
