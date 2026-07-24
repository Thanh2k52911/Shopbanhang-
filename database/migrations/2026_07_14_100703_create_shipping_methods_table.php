<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_methods', function (Blueprint $table) {
            $table->id();

            $table->string('name', 150);
            $table->string('code', 50)->unique();

            /*
             * Ví dụ:
             * internal
             * ghn
             * ghtk
             * viettel_post
             * jandt
             */
            $table->string('provider', 100)->nullable();

            $table->text('description')->nullable();

            // Phí giao hàng cơ bản
            $table->decimal('base_fee', 15, 2)->default(0);

            // Giá trị đơn tối thiểu để được miễn phí giao
            $table->decimal('free_shipping_minimum', 15, 2)->nullable();

            // Thời gian giao dự kiến
            $table->unsignedInteger('estimated_days_min')->nullable();
            $table->unsignedInteger('estimated_days_max')->nullable();

            $table->boolean('status')->default(true);
            $table->integer('sort_order')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_methods');
    }
};
