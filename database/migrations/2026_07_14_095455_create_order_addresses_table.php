<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_addresses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            /*
             * shipping: địa chỉ nhận hàng
             * billing: địa chỉ xuất hóa đơn
             */
            $table->string('type', 20)->default('shipping');

            $table->string('receiver_name', 150);
            $table->string('phone', 20);
            $table->string('email')->nullable();

            $table->string('province', 150);
            $table->string('district', 150);
            $table->string('ward', 150);
            $table->string('address', 255);

            // Lưu địa chỉ đầy đủ để hiển thị nhanh
            $table->text('full_address')->nullable();

            $table->text('note')->nullable();

            $table->timestamps();

            // Một đơn chỉ có một địa chỉ cho mỗi loại
            $table->unique(['order_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_addresses');
    }
};
