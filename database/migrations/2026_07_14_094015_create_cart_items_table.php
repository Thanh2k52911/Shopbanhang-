<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cart_id')
                ->constrained('carts')
                ->cascadeOnDelete();

            /*
             * Database của bạn đã có product_skus.
             * Mỗi dòng trong giỏ nên trỏ trực tiếp tới SKU.
             */
            $table->foreignId('sku_id')
                ->constrained('product_skus')
                ->cascadeOnDelete();

            $table->unsignedInteger('quantity')->default(1);

            // Giá tại thời điểm thêm vào giỏ
            $table->decimal('unit_price', 12, 2);

            // Số tiền giảm trên một sản phẩm
            $table->decimal('discount_amount', 12, 2)->default(0);

            $table->timestamps();

            // Một SKU chỉ xuất hiện một lần trong cùng giỏ hàng
            $table->unique(['cart_id', 'sku_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
