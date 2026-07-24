<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductQuestionAnswerSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('users')
            ->orderBy('id')
            ->value('id');

        if (!$adminId) {
            $this->command?->warn(
                'Không có user để tạo câu trả lời.'
            );

            return;
        }

        $answers = [
            [
                'question' => 'Sản phẩm này có phù hợp với da dầu mụn không?',
                'answer' => 'Sản phẩm phù hợp với da thường, da dầu và da hỗn hợp. Với da đang có tình trạng mụn viêm, bạn nên thử trước trên vùng da nhỏ.',
            ],
            [
                'question' => 'Da khô có sử dụng sản phẩm này hằng ngày được không?',
                'answer' => 'Da khô vẫn có thể sử dụng, tuy nhiên nên tạo bọt kỹ và dùng thêm toner cùng kem dưỡng sau khi rửa mặt.',
            ],
            [
                'question' => 'Kem chống nắng có nâng tông da không?',
                'answer' => 'Sản phẩm gần như không nâng tông rõ rệt và cho bề mặt khá tự nhiên khi được tán đều.',
            ],
            [
                'question' => 'Sản phẩm có chống nước khi đi biển không?',
                'answer' => 'Sản phẩm có khả năng chống nước tốt, nhưng bạn vẫn nên thoa lại sau khi bơi hoặc lau người bằng khăn.',
            ],
        ];

        foreach ($answers as $answer) {
            $question = DB::table('product_questions')
                ->where('question', $answer['question'])
                ->first();

            if (!$question) {
                $this->command?->warn(
                    "Không tìm thấy câu hỏi: {$answer['question']}"
                );

                continue;
            }

            DB::table('product_question_answers')->updateOrInsert(
                [
                    'question_id' => $question->id,
                    'user_id' => $adminId,
                    'is_official' => true,
                ],
                [
                    'answer' => $answer['answer'],
                    'status' => true,
                    'created_at' => $question->answered_at
                        ?? now(),
                    'updated_at' => now(),
                    'deleted_at' => null,
                ]
            );

            DB::table('product_questions')
                ->where('id', $question->id)
                ->update([
                    'status' => 'answered',
                    'answered_at' => $question->answered_at
                        ?? now(),
                    'updated_at' => now(),
                ]);
        }
    }
}
