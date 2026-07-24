<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(
            "ALTER TABLE carts MODIFY status ENUM('active', 'buy_now', 'converted', 'abandoned', 'expired') NOT NULL DEFAULT 'active'"
        );
    }

    public function down(): void
    {
        DB::table('carts')
            ->where('status', 'buy_now')
            ->update([
                'status' => 'abandoned',
                'updated_at' => now(),
            ]);

        DB::statement(
            "ALTER TABLE carts MODIFY status ENUM('active', 'converted', 'abandoned', 'expired') NOT NULL DEFAULT 'active'"
        );
    }
};
