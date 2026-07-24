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
        Schema::create('product_discounts', function(Blueprint $table){

    $table->id();



    $table->foreignId('campaign_id')
        ->constrained('discount_campaigns')
        ->cascadeOnDelete();



    $table->foreignId('product_id')
        ->constrained()
        ->cascadeOnDelete();



    $table->decimal('discount_percent',5,2)
        ->nullable();



    $table->decimal('discount_amount',10,2)
        ->nullable();



    $table->integer('limit_quantity')
        ->nullable();



    $table->integer('sold_quantity')
        ->default(0);



    $table->timestamps();


});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_discounts');
    }
};
