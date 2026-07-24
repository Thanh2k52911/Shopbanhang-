<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_messages', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('conversation_id')
                ->constrained('support_conversations')
                ->cascadeOnDelete();

            $table->foreignId('sender_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->text('message');

            $table->boolean('is_read_by_customer')
                ->default(false);

            $table->boolean('is_read_by_shop')
                ->default(false);

            $table->timestamps();

            $table->index(
                ['conversation_id', 'created_at'],
                'support_messages_conversation_created_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_messages');
    }
};
