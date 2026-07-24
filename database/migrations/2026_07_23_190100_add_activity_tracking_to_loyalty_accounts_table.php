<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loyalty_accounts', function (Blueprint $table): void {
            $table->foreignId('highest_tier_id')
                ->nullable()
                ->after('tier_id')
                ->constrained('loyalty_tiers')
                ->nullOnDelete();

            $table->timestamp('last_completed_order_at')->nullable()->after('tier_expires_at');
            $table->timestamp('inactive_downgraded_at')->nullable()->after('last_completed_order_at');

            $table->index('highest_tier_id');
            $table->index('last_completed_order_at');
        });

        DB::table('loyalty_accounts')
            ->whereNull('highest_tier_id')
            ->update([
                'highest_tier_id' => DB::raw('tier_id'),
            ]);

        DB::table('loyalty_accounts')
            ->orderBy('id')
            ->chunkById(100, function ($accounts): void {
                foreach ($accounts as $account) {
                    $lastCompletedAt = DB::table('orders')
                        ->where('user_id', $account->user_id)
                        ->where('order_status', 'completed')
                        ->max('completed_at');

                    if ($lastCompletedAt !== null) {
                        DB::table('loyalty_accounts')
                            ->where('id', $account->id)
                            ->update([
                                'last_completed_order_at' => $lastCompletedAt,
                            ]);
                    }
                }
            });
    }

    public function down(): void
    {
        Schema::table('loyalty_accounts', function (Blueprint $table): void {
            $table->dropIndex(['last_completed_order_at']);
            $table->dropForeign(['highest_tier_id']);
            $table->dropColumn([
                'highest_tier_id',
                'last_completed_order_at',
                'inactive_downgraded_at',
            ]);
        });
    }
};
