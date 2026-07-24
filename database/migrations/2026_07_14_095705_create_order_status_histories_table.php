<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            // Trạng thái trước khi thay đổi
            $table->string('from_status', 30)->nullable();

            // Trạng thái sau khi thay đổi
            $table->string('to_status', 30);

            /*
             * Phân loại trạng thái được thay đổi:
             * order, payment hoặc shipping
             */
            $table->string('status_type', 30)->default('order');

            $table->text('note')->nullable();

            // Người thực hiện thay đổi
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            /*
             * Nguồn thay đổi:
             * admin, customer, system, shipping_provider
             */
            $table->string('source', 30)->default('system');

            $table->timestamps();

            $table->index(['order_id', 'status_type']);
            $table->index(['order_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_status_histories');
    }
};
