<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_question_answers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('question_id')
                ->constrained('product_questions')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('answer');

            // Câu trả lời chính thức từ cửa hàng
            $table->boolean('is_official')->default(false);

            $table->boolean('status')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->index(
                ['question_id', 'is_official'],
                'question_answers_official_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_question_answers');
    }
};
