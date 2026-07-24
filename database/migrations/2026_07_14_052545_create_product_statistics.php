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
        Schema::create('product_statistics', function(Blueprint $table){


    $table->id();


    $table->foreignId('product_id')
        ->constrained()
        ->cascadeOnDelete();



    $table->integer('views')
        ->default(0);


    $table->integer('favorites')
        ->default(0);


    $table->integer('orders')
        ->default(0);



    $table->integer('sold_quantity')
        ->default(0);



    $table->decimal('revenue',12,2)
        ->default(0);



    $table->timestamps();


});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_statistics');
    }
};
