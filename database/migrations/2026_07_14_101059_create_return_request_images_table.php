<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('return_request_images', function (Blueprint $table) {
            $table->id();

            $table->foreignId('return_request_id')
                ->constrained('return_requests')
                ->cascadeOnDelete();

            // Có thể gắn ảnh vào từng sản phẩm cụ thể
            $table->foreignId('return_request_item_id')
                ->nullable()
                ->constrained('return_request_items')
                ->cascadeOnDelete();

            $table->string('image_path', 500);

            $table->string('caption', 255)->nullable();

            /*
             * customer: ảnh khách gửi
             * admin: ảnh Admin chụp khi kiểm tra hàng
             */
            $table->string('uploaded_by_type', 30)
                ->default('customer');

            $table->foreignId('uploaded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(
         ['return_request_id', 'return_request_item_id'],
                'return_images_request_item_idx'
        );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('return_request_images');
    }
};
