<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();

            $table->string('title', 255);

            $table->string('slug', 255)->unique();

            $table->longText('content')->nullable();

            // Ảnh đại diện của trang
            $table->string('thumbnail', 500)->nullable();

            /*
             * normal: trang nội dung thường
             * policy: trang chính sách
             * guide: trang hướng dẫn
             * about: trang giới thiệu
             */
            $table->string('page_type', 50)->default('normal');

            // Thông tin SEO
            $table->string('meta_title', 255)->nullable();

            $table->text('meta_description')->nullable();

            $table->string('meta_keywords', 500)->nullable();

            // Giao diện riêng nếu cần
            $table->string('template', 100)->nullable();

            $table->boolean('show_in_header')->default(false);

            $table->boolean('show_in_footer')->default(false);

            $table->boolean('status')->default(true);

            $table->unsignedInteger('sort_order')->default(0);

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
                ['page_type', 'status'],
                'pages_type_status_idx'
            );

            $table->index(
                ['show_in_header', 'sort_order'],
                'pages_header_sort_idx'
            );

            $table->index(
                ['show_in_footer', 'sort_order'],
                'pages_footer_sort_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
