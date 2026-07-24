<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loyalty_tier_rewards', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('loyalty_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('loyalty_tier_id')->constrained()->cascadeOnDelete();
            $table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('awarded_at');
            $table->timestamps();

            $table->unique(
                ['loyalty_account_id', 'loyalty_tier_id'],
                'loyalty_account_tier_reward_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_tier_rewards');
    }
};
