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
        Schema::create('inventory_transactions', function(Blueprint $table){

    $table->id();



    $table->foreignId('warehouse_id')
        ->constrained();



    $table->foreignId('sku_id')
        ->constrained('product_skus');



    $table->enum('type',[

        'import',
        'export',
        'return',
        'cancel',
        'adjust'

    ]);



    $table->integer('quantity');



    $table->string('reference_type')
        ->nullable();


    $table->bigInteger('reference_id')
        ->nullable();



    $table->text('note')
        ->nullable();



    $table->foreignId('created_by')
        ->nullable()
        ->constrained('users')
        ->nullOnDelete();



    $table->timestamps();

});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
    }
};
