<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductQuestion;
use App\Services\Admin\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class ProductQuestionController extends Controller
{
    public function __construct(
        private readonly NotificationService $notificationService
    ) {
    }

    public function store(
        Request $request,
        Product $product
    ): JsonResponse|RedirectResponse {
        abort_if(
            ! $product->status
            || $product->deleted_at !== null,
            404
        );

        $rules = [
            'question' => [
                'required',
                'string',
                'min:2',
                'max:2000',
            ],
        ];

        if (! $request->user()) {
            $rules['guest_name'] = [
                'required',
                'string',
                'min:2',
                'max:150',
            ];

            $rules['guest_email'] = [
                'required',
                'email',
                'max:255',
            ];
        }

        $validated = $request->validate(
            $rules,
            [
                'question.required' =>
                    'Vui lòng nhập nội dung.',

                'question.min' =>
                    'Nội dung phải có ít nhất 2 ký tự.',

                'question.max' =>
                    'Nội dung không được vượt quá 2.000 ký tự.',

                'guest_name.required' =>
                    'Vui lòng nhập tên của bạn.',

                'guest_email.required' =>
                    'Vui lòng nhập email của bạn.',

                'guest_email.email' =>
                    'Email không đúng định dạng.',
            ]
        );

        $question = ProductQuestion::query()->create([
            'product_id' => $product->id,
            'user_id' => $request->user()?->id,

            'guest_name' => $request->user()
                ? null
                : trim($validated['guest_name']),

            'guest_email' => $request->user()
                ? null
                : mb_strtolower(
                    trim($validated['guest_email'])
                ),

            'question' => trim($validated['question']),

            /*
             * Cộng đồng công khai:
             * đăng xong hiển thị ngay, không cần Admin duyệt.
             */
            'status' => 'published',
            'is_public' => true,
            'answered_at' => null,
        ]);

        $this->notificationService->safely(
            fn () => $this->notificationService
                ->notifyNewQuestion(
                    $question->id,
                    $product->name,
                    $question->question,
                    [
                        'product_id' => $product->id,
                        'user_id' => $question->user_id,
                    ]
                )
        );

        $payload = $this->questionPayload(
            $request,
            $question
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Nội dung đã được đăng.',
                'question' => $payload,
            ]);
        }

        return back()->with(
            'question_success',
            'Nội dung đã được đăng.'
        );
    }

    public function reply(
        Request $request,
        ProductQuestion $question
    ): JsonResponse|RedirectResponse {
        abort_unless(
            $question->is_public
            && in_array(
                $question->status,
                ['published', 'answered'],
                true
            ),
            404
        );

        if (! $request->user()) {
            abort(401);
        }

        $validated = $request->validate(
            [
                'answer' => [
                    'required',
                    'string',
                    'min:1',
                    'max:3000',
                ],
            ],
            [
                'answer.required' =>
                    'Vui lòng nhập nội dung trả lời.',

                'answer.max' =>
                    'Câu trả lời không được vượt quá 3.000 ký tự.',
            ]
        );

        $user = $request->user();
        $isShop = $this->isShopUser($user);

        try {
            $answerId = DB::transaction(
                function () use (
                    $question,
                    $user,
                    $validated,
                    $isShop
                ): int {
                    $answerId = DB::table(
                        'product_question_answers'
                    )->insertGetId([
                        'question_id' => $question->id,
                        'user_id' => $user->id,
                        'answer' => trim($validated['answer']),
                        'is_official' => $isShop,
                        'status' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    DB::table('product_questions')
                        ->where('id', $question->id)
                        ->update([
                            'status' => 'answered',
                            'answered_at' =>
                                $question->answered_at
                                ?? now(),
                            'updated_at' => now(),
                        ]);

                    return $answerId;
                },
                3
            );
        } catch (Throwable $exception) {
            report($exception);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' =>
                        'Không thể gửi câu trả lời lúc này.',
                ], 500);
            }

            return back()->with(
                'error',
                'Không thể gửi câu trả lời lúc này.'
            );
        }

        $answer = DB::table(
            'product_question_answers'
        )
            ->where('id', $answerId)
            ->first();

        $payload = [
            'id' => $answerId,
            'answer' => $answer->answer,
            'author_name' => $isShop
                ? site_name()
                : $user->name,
            'author_avatar' => $isShop
                ? null
                : (
                    $user->avatar
                        ? asset(
                            'storage/'
                            . ltrim($user->avatar, '/')
                        )
                        : null
                ),
            'is_shop' => $isShop,
            'created_at' => now()->format('d/m/Y H:i'),
        ];

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã gửi câu trả lời.',
                'answer' => $payload,
            ]);
        }

        return back()->with(
            'success',
            'Đã gửi câu trả lời.'
        );
    }

    private function questionPayload(
        Request $request,
        ProductQuestion $question
    ): array {
        $user = $request->user();
        $isShop = $user
            ? $this->isShopUser($user)
            : false;

        return [
            'id' => $question->id,
            'question' => $question->question,
            'author_name' => $isShop
                ? site_name()
                : (
                    $user?->name
                    ?? $question->guest_name
                    ?? 'Khách hàng'
                ),
            'author_avatar' => (
                $user
                && ! $isShop
                && $user->avatar
            )
                ? asset(
                    'storage/'
                    . ltrim($user->avatar, '/')
                )
                : null,
            'is_shop' => $isShop,
            'created_at' =>
                $question->created_at->format(
                    'd/m/Y H:i'
                ),
            'reply_url' => route(
                'products.questions.reply',
                $question
            ),
        ];
    }

    private function isShopUser($user): bool
    {
        $user->loadMissing('roles:id,name');

        return $user->roles->contains(
            fn ($role): bool => in_array(
                $role->name,
                ['admin', 'super_admin'],
                true
            )
        );
    }
}
