<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saved_coupons', function (
            Blueprint $table
        ): void {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('coupon_id')
                ->constrained('coupons')
                ->cascadeOnDelete();

            $table->timestamp('saved_at')
                ->useCurrent();

            $table->timestamps();

            $table->unique([
                'user_id',
                'coupon_id',
            ]);

            $table->index([
                'user_id',
                'saved_at',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_coupons');
    }
};
