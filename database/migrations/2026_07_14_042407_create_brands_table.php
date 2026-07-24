<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('brands', function (Blueprint $table) {

        $table->id();

        $table->string('name', 120);

        $table->string('slug')->unique();

        $table->string('thumbnail')->nullable();

        $table->string('country', 100)->nullable();

        $table->string('website')->nullable();

        $table->text('description')->nullable();

        $table->integer('sort_order')->default(0);

        $table->tinyInteger('status')
            ->default(1)
            ->comment('0: Ẩn, 1: Hiển thị');

        $table->timestamps();

        $table->softDeletes();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brands');
    }
};
