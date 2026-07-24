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
    Schema::create('suppliers', function (Blueprint $table) {

        $table->id();

        $table->string('name');

        $table->string('contact_name')->nullable();

        $table->string('phone', 15)->nullable();

        $table->string('email')->nullable();

        $table->string('address')->nullable();

        $table->string('tax_code')->nullable();

        $table->integer('sort_order')->default(0);

        $table->tinyInteger('status')
            ->default(1)
            ->comment('0: Ngừng hợp tác, 1: Đang hợp tác');

        $table->timestamps();

        $table->softDeletes();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
