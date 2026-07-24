<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();

            /*
             * Nhóm cấu hình:
             * general
             * contact
             * social
             * payment
             * shipping
             * seo
             * email
             */
            $table->string('group', 100)->default('general');

            // Ví dụ: site_name, site_logo, hotline
            $table->string('key', 255)->unique();

            $table->longText('value')->nullable();

            /*
             * string, text, number, boolean,
             * json, image, file, color
             */
            $table->string('type', 30)->default('string');

            $table->string('label', 255)->nullable();

            $table->text('description')->nullable();

            // Có cho phép frontend đọc cấu hình này không
            $table->boolean('is_public')->default(false);

            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(
                ['group', 'sort_order'],
                'settings_group_sort_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
