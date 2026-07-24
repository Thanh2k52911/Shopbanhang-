<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('return_requests', function (Blueprint $table) {
            $table->id();

            // Mã yêu cầu đổi trả hiển thị cho khách
            $table->string('return_code', 50)->unique();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            /*
             * return: trả hàng
             * exchange: đổi sản phẩm
             * refund: chỉ yêu cầu hoàn tiền
             */
            $table->string('request_type', 30)->default('return');

            /*
             * pending: chờ xử lý
             * approved: đã chấp nhận
             * rejected: từ chối
             * waiting_for_return: chờ khách gửi hàng
             * returning: hàng đang được gửi trả
             * received: cửa hàng đã nhận hàng
             * inspecting: đang kiểm tra sản phẩm
             * processing: đang xử lý
             * completed: hoàn tất
             * cancelled: khách hoặc Admin hủy
             */
            $table->string('status', 30)->default('pending');

            // Lý do chính
            $table->string('reason', 255);

            // Nội dung mô tả chi tiết
            $table->text('description')->nullable();

            // Tổng số tiền khách yêu cầu hoàn
            $table->decimal('requested_amount', 15, 2)->default(0);

            // Số tiền Admin chấp nhận hoàn
            $table->decimal('approved_amount', 15, 2)->default(0);

            // Phí vận chuyển trả hàng
            $table->decimal('return_shipping_fee', 15, 2)->default(0);

            /*
             * customer: khách chịu phí
             * shop: cửa hàng chịu phí
             */
            $table->string('shipping_fee_payer', 30)
                ->nullable();

            $table->text('customer_note')->nullable();
            $table->text('admin_note')->nullable();
            $table->text('rejection_reason')->nullable();

            // Nhân viên xử lý yêu cầu
            $table->foreignId('processed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->timestamps();

            $table->index(['order_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('return_requests');
    }
};
