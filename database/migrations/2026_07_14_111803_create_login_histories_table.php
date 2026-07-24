<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('login_histories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnDelete();

            // Lưu cả email khi đăng nhập thất bại
            $table->string('email')->nullable();

            $table->string('session_id', 255)->nullable();

            $table->string('ip_address', 45)->nullable();

            $table->text('user_agent')->nullable();

            $table->string('device', 150)->nullable();
            $table->string('browser', 100)->nullable();
            $table->string('platform', 100)->nullable();

            $table->string('country', 100)->nullable();
            $table->string('city', 100)->nullable();

            $table->boolean('is_success')->default(true);

            $table->string('failure_reason', 255)->nullable();

            $table->timestamp('logged_in_at')->useCurrent();
            $table->timestamp('logged_out_at')->nullable();

            $table->timestamps();

            $table->index(
                ['user_id', 'logged_in_at'],
                'login_histories_user_time_idx'
            );

            $table->index(
                ['email', 'is_success', 'logged_in_at'],
                'login_histories_email_success_idx'
            );

            $table->index(
                ['ip_address', 'logged_in_at'],
                'login_histories_ip_time_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_histories');
    }
};
