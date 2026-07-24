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
        Schema::create('product_reviews', function(Blueprint $table){


    $table->id();



    $table->foreignId('product_id')
        ->constrained()
        ->cascadeOnDelete();



    $table->foreignId('user_id')
        ->constrained()
        ->cascadeOnDelete();



    // đơn hàng đã mua

    $table->unsignedBigInteger('order_id')
    ->nullable();



    $table->tinyInteger('rating');



    $table->text('content')
        ->nullable();



    $table->boolean('verified_purchase')
        ->default(false);



    // review được duyệt

    $table->boolean('status')
        ->default(1);



    $table->timestamps();



});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_reviews');
    }
};
