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
        Schema::create('product_skus', function(Blueprint $table){

    $table->id();


    $table->foreignId('product_id')
        ->constrained()
        ->cascadeOnDelete();



    $table->foreignId('variant_id')
        ->nullable()
        ->constrained('product_variants')
        ->nullOnDelete();



    $table->string('sku_code')
        ->unique();


    $table->string('barcode')
        ->nullable();



    $table->decimal('price',10,2);


    $table->decimal('cost_price',10,2)
        ->nullable();



    $table->integer('weight')
        ->nullable();



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
        Schema::dropIfExists('_product__s_k_us');
    }
};
