<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loyalty_tiers', function (Blueprint $table): void {
            $table->boolean('reward_enabled')->default(false)->after('discount_percent');
            $table->string('reward_name', 255)->nullable()->after('reward_enabled');
            $table->text('reward_description')->nullable()->after('reward_name');
            $table->string('reward_discount_type', 30)->nullable()->after('reward_description');
            $table->decimal('reward_discount_value', 15, 2)->nullable()->after('reward_discount_type');
            $table->decimal('reward_maximum_discount', 15, 2)->nullable()->after('reward_discount_value');
            $table->decimal('reward_minimum_order_amount', 15, 2)->default(0)->after('reward_maximum_discount');
            $table->unsignedInteger('reward_valid_days')->default(30)->after('reward_minimum_order_amount');
        });
    }

    public function down(): void
    {
        Schema::table('loyalty_tiers', function (Blueprint $table): void {
            $table->dropColumn([
                'reward_enabled',
                'reward_name',
                'reward_description',
                'reward_discount_type',
                'reward_discount_value',
                'reward_maximum_discount',
                'reward_minimum_order_amount',
                'reward_valid_days',
            ]);
        });
    }
};
