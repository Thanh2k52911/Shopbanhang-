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
        Schema::create('products', function (Blueprint $table) {

    $table->id();

    $table->foreignId('category_id')
        ->constrained()
        ->cascadeOnDelete();

    $table->foreignId('brand_id')
        ->nullable()
        ->constrained()
        ->nullOnDelete();

    $table->foreignId('supplier_id')
        ->nullable()
        ->constrained()
        ->nullOnDelete();


    $table->string('name');

    $table->string('slug')
        ->unique();


    $table->text('short_description')
        ->nullable();

    $table->longText('description')
        ->nullable();


    // mỹ phẩm
    $table->longText('ingredient')
        ->nullable();

    $table->longText('usage')
        ->nullable();


    $table->string('skin_type')
        ->nullable();

    $table->string('origin')
        ->nullable();



    $table->boolean('status')
        ->default(1);


    $table->boolean('is_featured')
        ->default(false);


    $table->unsignedInteger('view_count')
        ->default(0);



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

});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
