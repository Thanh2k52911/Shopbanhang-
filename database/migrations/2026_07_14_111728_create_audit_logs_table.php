<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            /*
             * created, updated, deleted, restored,
             * login, logout, approved, rejected
             */
            $table->string('action', 50);

            /*
             * Ví dụ:
             * App\Models\Product
             * App\Models\Order
             */
            $table->string('auditable_type', 255)->nullable();

            $table->unsignedBigInteger('auditable_id')->nullable();

            $table->string('description', 500)->nullable();

            // Dữ liệu trước khi thay đổi
            $table->json('old_values')->nullable();

            // Dữ liệu sau khi thay đổi
            $table->json('new_values')->nullable();

            $table->string('route_name', 255)->nullable();
            $table->string('url', 1000)->nullable();
            $table->string('request_method', 10)->nullable();

            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamps();

            $table->index(
                ['auditable_type', 'auditable_id'],
                'audit_logs_auditable_idx'
            );

            $table->index(
                ['user_id', 'action', 'created_at'],
                'audit_logs_user_action_idx'
            );

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
