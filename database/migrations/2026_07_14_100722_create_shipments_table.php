<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            $table->foreignId('shipping_method_id')
                ->nullable()
                ->constrained('shipping_methods')
                ->nullOnDelete();

            $table->foreignId('warehouse_id')
                ->nullable()
                ->constrained('warehouses')
                ->nullOnDelete();

            // Mã kiện hàng nội bộ
            $table->string('shipment_code', 50)->unique();

            // Mã vận đơn từ đơn vị vận chuyển
            $table->string('tracking_code', 100)
                ->nullable()
                ->index();

            $table->string('carrier_name', 150)->nullable();
            $table->string('service_name', 150)->nullable();

            /*
             * pending: chờ xử lý
             * ready_to_ship: chờ bàn giao
             * picked_up: shipper đã lấy hàng
             * in_transit: đang vận chuyển
             * out_for_delivery: đang giao tới khách
             * delivered: giao thành công
             * delivery_failed: giao thất bại
             * cancelled: đã hủy
             * returned: hoàn về cửa hàng
             */
            $table->string('status', 30)->default('pending');

            $table->decimal('shipping_fee', 15, 2)->default(0);

            // Tiền shipper cần thu hộ
            $table->decimal('cod_amount', 15, 2)->default(0);

            // Tổng trọng lượng kiện hàng, tính theo gram
            $table->unsignedInteger('weight')->nullable();

            $table->unsignedInteger('length')->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();

            $table->text('note')->nullable();

            // Dữ liệu phản hồi từ đơn vị giao hàng
            $table->json('provider_data')->nullable();

            $table->timestamp('estimated_delivery_at')->nullable();
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->timestamps();

            $table->index(['order_id', 'status']);
            $table->index(['shipping_method_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
