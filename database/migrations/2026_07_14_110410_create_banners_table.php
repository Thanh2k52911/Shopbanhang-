<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();

            $table->string('name', 255);

            $table->string('title', 255)->nullable();

            $table->string('subtitle', 255)->nullable();

            // Ảnh hiển thị trên máy tính
            $table->string('desktop_image', 500);

            // Ảnh riêng cho điện thoại
            $table->string('mobile_image', 500)->nullable();

            // Đường dẫn khi khách bấm banner
            $table->string('link_url', 1000)->nullable();

            $table->string('button_text', 100)->nullable();

            /*
             * home_slider: slider đầu trang chủ
             * home_middle: banner giữa trang chủ
             * home_bottom: banner cuối trang
             * category: banner danh mục
             * product: banner trang sản phẩm
             * popup: banner popup
             */
            $table->string('position', 50)->default('home_slider');

            /*
             * _self: mở cùng tab
             * _blank: mở tab mới
             */
            $table->string('target', 20)->default('_self');

            $table->unsignedInteger('sort_order')->default(0);

            $table->boolean('status')->default(true);

            // Thời gian banner bắt đầu và kết thúc hiển thị
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(
                ['position', 'status', 'sort_order'],
                'banners_position_status_sort_idx'
            );

            $table->index(
                ['start_at', 'end_at'],
                'banners_display_time_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
