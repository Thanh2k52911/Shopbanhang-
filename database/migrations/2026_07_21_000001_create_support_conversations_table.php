<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_conversations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('assigned_admin_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('subject', 255)
                ->default('Hỗ trợ khách hàng');

            $table->enum('status', [
                'open',
                'waiting_customer',
                'waiting_shop',
                'closed',
            ])->default('open');

            $table->timestamp('last_message_at')
                ->nullable()
                ->index();

            $table->timestamp('closed_at')
                ->nullable();

            $table->timestamps();

            $table->index(
                ['user_id', 'status'],
                'support_conversations_user_status_index'
            );

            $table->index(
                ['assigned_admin_id', 'status'],
                'support_conversations_admin_status_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_conversations');
    }
};
