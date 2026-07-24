<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bổ sung các trường phục vụ quản trị và bảo mật tài khoản.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table
                ->string('status', 20)
                ->default('active')
                ->after('email_verified_at')
                ->index();

            $table
                ->timestamp('blocked_at')
                ->nullable()
                ->after('status');

            $table
                ->text('blocked_reason')
                ->nullable()
                ->after('blocked_at');

            $table
                ->timestamp('last_login_at')
                ->nullable()
                ->after('remember_token');

            $table
                ->string('last_login_ip', 45)
                ->nullable()
                ->after('last_login_at');

            $table->softDeletes();
        });
    }

    /**
     * Hoàn tác migration.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex(['status']);

            $table->dropColumn([
                'status',
                'blocked_at',
                'blocked_reason',
                'last_login_at',
                'last_login_ip',
                'deleted_at',
            ]);
        });
    }
};
