<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('newsletter_subscribers', function (Blueprint $table) {
            $table->id();

            $table->string('email')->unique();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('name', 150)->nullable();

            $table->boolean('status')->default(true);

            // Nguồn đăng ký: footer, popup, checkout...
            $table->string('source', 50)->nullable();

            $table->string('verification_token', 100)
                ->nullable()
                ->unique();

            $table->string('unsubscribe_token', 100)
                ->unique();

            $table->timestamp('verified_at')->nullable();
            $table->timestamp('subscribed_at')->useCurrent();
            $table->timestamp('unsubscribed_at')->nullable();

            $table->timestamps();

            $table->index(
                ['status', 'subscribed_at'],
                'newsletter_status_subscribed_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletter_subscribers');
    }
};
