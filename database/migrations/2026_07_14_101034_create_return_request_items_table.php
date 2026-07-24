<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('return_request_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('return_request_id')
                ->constrained('return_requests')
                ->cascadeOnDelete();

            $table->foreignId('order_item_id')
                ->constrained('order_items')
                ->cascadeOnDelete();

            // Số lượng khách muốn trả
            $table->unsignedInteger('quantity');

            $table->string('reason', 255)->nullable();
            $table->text('description')->nullable();

            /*
             * unopened: chưa mở
             * opened: đã mở
             * damaged: hư hỏng
             * defective: lỗi sản phẩm
             * wrong_item: giao sai
             * expired: hết hạn
             * allergic: gây kích ứng
             * other: lý do khác
             */
            $table->string('product_condition', 30)
                ->nullable();

            // Số tiền hoàn cho riêng dòng sản phẩm
            $table->decimal('requested_refund_amount', 15, 2)
                ->default(0);

            $table->decimal('approved_refund_amount', 15, 2)
                ->default(0);

            // Kết quả kiểm tra của Admin
            $table->string('inspection_result', 50)
                ->nullable();

            $table->text('inspection_note')->nullable();

            /*
             * restock: nhập lại kho
             * damaged: đưa vào hàng hỏng
             * disposal: tiêu hủy
             * supplier_return: trả nhà cung cấp
             */
            $table->string('inventory_action', 30)
                ->nullable();

            $table->timestamps();

            $table->unique(
                ['return_request_id', 'order_item_id'],
                'return_request_order_item_unique'
            );

            $table->index('order_item_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('return_request_items');
    }
};
