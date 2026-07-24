<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // Mã đơn hàng hiển thị cho khách
            $table->string('order_code', 50)->unique();

            // Cho phép khách chưa đăng nhập đặt hàng
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Mã giảm giá đã áp dụng
            $table->foreignId('coupon_id')
                ->nullable()
                ->constrained('coupons')
                ->nullOnDelete();

            // Kho xử lý đơn hàng
            $table->foreignId('warehouse_id')
                ->nullable()
                ->constrained('warehouses')
                ->nullOnDelete();

            /*
             * Trạng thái đơn hàng:
             * pending: chờ xác nhận
             * confirmed: đã xác nhận
             * processing: đang xử lý
             * packed: đã đóng gói
             * shipping: đang giao
             * completed: hoàn thành
             * cancelled: đã hủy
             * returned: đã trả hàng
             */
            $table->string('order_status', 30)->default('pending');

            /*
             * Trạng thái thanh toán:
             * unpaid, pending, paid, failed,
             * cancelled, refunded, partially_refunded
             */
            $table->string('payment_status', 30)->default('unpaid');

            /*
             * Trạng thái giao hàng:
             * pending, ready_to_ship, picked_up,
             * in_transit, delivered, failed, returned
             */
            $table->string('shipping_status', 30)->default('pending');

            // Phương thức thanh toán người dùng lựa chọn
            $table->string('payment_method', 30)->default('cod');

            // Tổng tiền sản phẩm trước giảm giá
            $table->decimal('subtotal', 15, 2)->default(0);

            // Tiền giảm trực tiếp từ chương trình khuyến mãi
            $table->decimal('product_discount', 15, 2)->default(0);

            // Tiền giảm từ coupon
            $table->decimal('coupon_discount', 15, 2)->default(0);

            // Phí vận chuyển
            $table->decimal('shipping_fee', 15, 2)->default(0);

            // Thuế nếu sau này cần sử dụng
            $table->decimal('tax_amount', 15, 2)->default(0);

            // Điểm thưởng được sử dụng
            $table->decimal('point_discount', 15, 2)->default(0);

            // Tổng tiền khách phải thanh toán
            $table->decimal('total_amount', 15, 2)->default(0);

            // Tổng số lượng sản phẩm
            $table->unsignedInteger('total_quantity')->default(0);

            // Thông tin liên hệ dự phòng cho khách chưa đăng nhập
            $table->string('customer_name', 150);
            $table->string('customer_email')->nullable();
            $table->string('customer_phone', 20);

            $table->text('customer_note')->nullable();
            $table->text('admin_note')->nullable();

            // Thông tin hủy đơn
            $table->text('cancel_reason')->nullable();

            $table->foreignId('cancelled_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Nhân viên xác nhận đơn
            $table->foreignId('confirmed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Dữ liệu hỗ trợ kiểm tra đơn hàng
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            // Các mốc thời gian xử lý
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('processing_at')->nullable();
            $table->timestamp('packed_at')->nullable();
            $table->timestamp('shipping_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Index phục vụ User và Admin
            $table->index(['user_id', 'order_status']);
            $table->index(['order_status', 'created_at']);
            $table->index(['payment_status', 'created_at']);
            $table->index(['shipping_status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
