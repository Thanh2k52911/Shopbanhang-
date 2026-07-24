<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class QuestionController extends Controller
{
    /**
     * Danh sách câu hỏi sản phẩm.
     */
    public function index(Request $request): View
    {
        $query = DB::table('product_questions as pq')
            ->join('products as p', 'pq.product_id', '=', 'p.id')
            ->leftJoin('users as u', 'pq.user_id', '=', 'u.id')
            ->whereNull('pq.deleted_at')
            ->whereNull('p.deleted_at')
            ->select([
                'pq.id',
                'pq.product_id',
                'pq.user_id',
                'pq.guest_name',
                'pq.guest_email',
                'pq.question',
                'pq.status',
                'pq.is_public',
                'pq.answered_at',
                'pq.created_at',
                'pq.updated_at',

                'p.name as product_name',
                'p.slug as product_slug',

                'u.name as user_name',
                'u.email as user_email',
                'u.avatar as user_avatar',
                'u.status as user_status',
            ])
            ->selectSub(
                function (Builder $builder): void {
                    $builder
                        ->from('product_question_answers')
                        ->selectRaw('COUNT(*)')
                        ->whereColumn(
                            'product_question_answers.question_id',
                            'pq.id'
                        )
                        ->whereNull(
                            'product_question_answers.deleted_at'
                        );
                },
                'answers_count'
            )
            ->selectSub(
                function (Builder $builder): void {
                    $builder
                        ->from('product_question_answers')
                        ->selectRaw('COUNT(*)')
                        ->whereColumn(
                            'product_question_answers.question_id',
                            'pq.id'
                        )
                        ->whereNull(
                            'product_question_answers.deleted_at'
                        )
                        ->where(
                            'product_question_answers.is_official',
                            true
                        )
                        ->where(
                            'product_question_answers.status',
                            true
                        );
                },
                'official_answers_count'
            )
            ->selectSub(
                function (Builder $builder): void {
                    $builder
                        ->from('product_question_answers')
                        ->select('created_at')
                        ->whereColumn(
                            'product_question_answers.question_id',
                            'pq.id'
                        )
                        ->whereNull(
                            'product_question_answers.deleted_at'
                        )
                        ->latest('created_at')
                        ->limit(1);
                },
                'last_answer_at'
            )
            ->selectSub(
                function (Builder $builder): void {
                    $builder
                        ->from('product_images')
                        ->select('image_path')
                        ->whereColumn(
                            'product_images.product_id',
                            'pq.product_id'
                        )
                        ->orderByDesc('is_thumbnail')
                        ->orderBy('sort_order')
                        ->orderBy('id')
                        ->limit(1);
                },
                'product_image'
            );

        $keyword = trim((string) $request->input('keyword'));

        if ($keyword !== '') {
            $query->where(
                function (Builder $builder) use ($keyword): void {
                    $builder
                        ->where(
                            'p.name',
                            'like',
                            '%' . $keyword . '%'
                        )
                        ->orWhere(
                            'pq.question',
                            'like',
                            '%' . $keyword . '%'
                        )
                        ->orWhere(
                            'u.name',
                            'like',
                            '%' . $keyword . '%'
                        )
                        ->orWhere(
                            'u.email',
                            'like',
                            '%' . $keyword . '%'
                        )
                        ->orWhere(
                            'pq.guest_name',
                            'like',
                            '%' . $keyword . '%'
                        )
                        ->orWhere(
                            'pq.guest_email',
                            'like',
                            '%' . $keyword . '%'
                        );
                }
            );
        }

        if ($request->filled('status')) {
            $query->where(
                'pq.status',
                $request->input('status')
            );
        }

        match ($request->input('visibility')) {
            'public' => $query->where('pq.is_public', true),
            'private' => $query->where('pq.is_public', false),
            default => null,
        };

        match ($request->input('answered')) {
            'yes' => $query->where(
                function (Builder $builder): void {
                    $builder
                        ->whereNotNull('pq.answered_at')
                        ->orWhereExists(
                            function (Builder $answerQuery): void {
                                $answerQuery
                                    ->selectRaw('1')
                                    ->from(
                                        'product_question_answers'
                                    )
                                    ->whereColumn(
                                        'product_question_answers.question_id',
                                        'pq.id'
                                    )
                                    ->whereNull(
                                        'product_question_answers.deleted_at'
                                    );
                            }
                        );
                }
            ),

            'no' => $query
                ->whereNull('pq.answered_at')
                ->whereNotExists(
                    function (Builder $builder): void {
                        $builder
                            ->selectRaw('1')
                            ->from('product_question_answers')
                            ->whereColumn(
                                'product_question_answers.question_id',
                                'pq.id'
                            )
                            ->whereNull(
                                'product_question_answers.deleted_at'
                            );
                    }
                ),

            default => null,
        };

        match ($request->input('author_type')) {
            'member' => $query->whereNotNull('pq.user_id'),
            'guest' => $query->whereNull('pq.user_id'),
            default => null,
        };

        match ($request->input('sort')) {
            'oldest' => $query->orderBy('pq.created_at'),
            'answers_desc' => $query
                ->orderByDesc('answers_count')
                ->orderByDesc('pq.created_at'),
            'unanswered_first' => $query
                ->orderByRaw(
                    "CASE
                        WHEN pq.answered_at IS NULL
                            AND NOT EXISTS (
                                SELECT 1
                                FROM product_question_answers pqa_sort
                                WHERE pqa_sort.question_id = pq.id
                                    AND pqa_sort.deleted_at IS NULL
                            )
                        THEN 0
                        ELSE 1
                    END"
                )
                ->orderByDesc('pq.created_at'),
            'last_answer_desc' => $query
                ->orderByDesc('last_answer_at')
                ->orderByDesc('pq.created_at'),
            default => $query->orderByDesc('pq.created_at'),
        };

        $questions = $query
            ->paginate(20)
            ->withQueryString();

        $statistics = DB::table('product_questions')
            ->whereNull('deleted_at')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw(
                "COALESCE(
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END),
                    0
                ) as pending"
            )
            ->selectRaw(
                "COALESCE(
                    SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END),
                    0
                ) as published"
            )
            ->selectRaw(
                "COALESCE(
                    SUM(CASE WHEN status = 'answered' THEN 1 ELSE 0 END),
                    0
                ) as answered"
            )
            ->selectRaw(
                "COALESCE(
                    SUM(CASE WHEN status = 'hidden' THEN 1 ELSE 0 END),
                    0
                ) as hidden"
            )
            ->selectRaw(
                "COALESCE(
                    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END),
                    0
                ) as rejected"
            )
            ->selectRaw(
                'COALESCE(
                    SUM(CASE WHEN is_public = 1 THEN 1 ELSE 0 END),
                    0
                ) as public_count'
            )
            ->first();

        $answerStatistics = [
            'total_answers' => DB::table(
                'product_question_answers'
            )
                ->whereNull('deleted_at')
                ->count(),

            'official_answers' => DB::table(
                'product_question_answers'
            )
                ->whereNull('deleted_at')
                ->where('is_official', true)
                ->where('status', true)
                ->count(),

            'unanswered_questions' => DB::table(
                'product_questions as pq'
            )
                ->whereNull('pq.deleted_at')
                ->whereNull('pq.answered_at')
                ->whereNotExists(
                    function (Builder $builder): void {
                        $builder
                            ->selectRaw('1')
                            ->from('product_question_answers')
                            ->whereColumn(
                                'product_question_answers.question_id',
                                'pq.id'
                            )
                            ->whereNull(
                                'product_question_answers.deleted_at'
                            );
                    }
                )
                ->count(),
        ];

        return view('admin.questions.index', [
            'questions' => $questions,
            'statistics' => $statistics,
            'answerStatistics' => $answerStatistics,
            'questionStatuses' => $this->questionStatuses(),
        ]);
    }

    /**
     * Chi tiết câu hỏi.
     */
    public function show(int $question): View
    {
        $questionDetail = DB::table('product_questions as pq')
            ->join('products as p', 'pq.product_id', '=', 'p.id')
            ->leftJoin('users as u', 'pq.user_id', '=', 'u.id')
            ->where('pq.id', $question)
            ->whereNull('pq.deleted_at')
            ->whereNull('p.deleted_at')
            ->select([
                'pq.id',
                'pq.product_id',
                'pq.user_id',
                'pq.guest_name',
                'pq.guest_email',
                'pq.question',
                'pq.status',
                'pq.is_public',
                'pq.answered_at',
                'pq.created_at',
                'pq.updated_at',

                'p.name as product_name',
                'p.slug as product_slug',
                'p.status as product_status',

                'u.name as user_name',
                'u.email as user_email',
                'u.avatar as user_avatar',
                'u.status as user_status',
                'u.last_login_at',
            ])
            ->selectSub(
                function (Builder $builder): void {
                    $builder
                        ->from('product_images')
                        ->select('image_path')
                        ->whereColumn(
                            'product_images.product_id',
                            'pq.product_id'
                        )
                        ->orderByDesc('is_thumbnail')
                        ->orderBy('sort_order')
                        ->orderBy('id')
                        ->limit(1);
                },
                'product_image'
            )
            ->first();

        abort_if(! $questionDetail, 404);

        $answers = DB::table(
            'product_question_answers as pqa'
        )
            ->leftJoin('users as u', 'pqa.user_id', '=', 'u.id')
            ->where(
                'pqa.question_id',
                $questionDetail->id
            )
            ->whereNull('pqa.deleted_at')
            ->orderBy('pqa.created_at')
            ->get([
                'pqa.id',
                'pqa.question_id',
                'pqa.user_id',
                'pqa.answer',
                'pqa.is_official',
                'pqa.status',
                'pqa.created_at',
                'pqa.updated_at',

                'u.name as user_name',
                'u.email as user_email',
                'u.avatar as user_avatar',
            ])
            ->map(function ($answer) {
                if ($answer->user_id !== null) {
                    $answer->roles = DB::table('user_roles as ur')
                        ->join(
                            'roles as r',
                            'ur.role_id',
                            '=',
                            'r.id'
                        )
                        ->where(
                            'ur.user_id',
                            $answer->user_id
                        )
                        ->pluck('r.name');
                } else {
                    $answer->roles = collect();
                }

                return $answer;
            });

        $relatedQuestions = DB::table(
            'product_questions as pq'
        )
            ->leftJoin('users as u', 'pq.user_id', '=', 'u.id')
            ->where(
                'pq.product_id',
                $questionDetail->product_id
            )
            ->where('pq.id', '!=', $questionDetail->id)
            ->whereNull('pq.deleted_at')
            ->orderByDesc('pq.created_at')
            ->limit(6)
            ->get([
                'pq.id',
                'pq.question',
                'pq.status',
                'pq.is_public',
                'pq.answered_at',
                'pq.created_at',
                'pq.guest_name',
                'u.name as user_name',
            ]);

        return view('admin.questions.show', [
            'question' => $questionDetail,
            'answers' => $answers,
            'relatedQuestions' => $relatedQuestions,
            'questionStatuses' => $this->questionStatuses(),
        ]);
    }

    /**
     * Cập nhật trạng thái và khả năng hiển thị của câu hỏi.
     */
    public function updateStatus(
        Request $request,
        int $question
    ): RedirectResponse {
        $questionDetail = DB::table('product_questions')
            ->where('id', $question)
            ->whereNull('deleted_at')
            ->first([
                'id',
                'status',
                'is_public',
                'answered_at',
            ]);

        abort_if(! $questionDetail, 404);

        $validated = $request->validate(
            [
                'status' => [
                    'required',
                    Rule::in(
                        array_keys(
                            $this->questionStatuses()
                        )
                    ),
                ],

                'is_public' => [
                    'nullable',
                    'boolean',
                ],
            ],
            [
                'status.required' =>
                    'Vui lòng chọn trạng thái câu hỏi.',

                'status.in' =>
                    'Trạng thái câu hỏi không hợp lệ.',
            ]
        );

        $newStatus = $validated['status'];
        $isPublic = (bool) (
            $validated['is_public'] ?? false
        );

        try {
            DB::table('product_questions')
                ->where('id', $questionDetail->id)
                ->update([
                    'status' => $newStatus,
                    'is_public' => $isPublic,
                    'answered_at' => $newStatus === 'answered'
                        ? (
                            $questionDetail->answered_at
                            ?? now()
                        )
                        : (
                            $newStatus === 'pending'
                                ? null
                                : $questionDetail->answered_at
                        ),
                    'updated_at' => now(),
                ]);

            return back()->with(
                'success',
                'Cập nhật trạng thái câu hỏi thành công.'
            );
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with(
                    'error',
                    app()->isLocal()
                        ? 'Lỗi cập nhật câu hỏi: '
                            . $exception->getMessage()
                        : 'Không thể cập nhật câu hỏi.'
                );
        }
    }

    /**
     * Tạo câu trả lời chính thức từ Admin.
     */
    public function answer(
        Request $request,
        int $question
    ): RedirectResponse {
        $questionDetail = DB::table('product_questions')
            ->where('id', $question)
            ->whereNull('deleted_at')
            ->first([
                'id',
                'status',
                'is_public',
                'answered_at',
            ]);

        abort_if(! $questionDetail, 404);

        $validated = $request->validate(
            [
                'answer' => [
                    'required',
                    'string',
                    'max:5000',
                ],

                'publish_question' => [
                    'nullable',
                    'boolean',
                ],
            ],
            [
                'answer.required' =>
                    'Vui lòng nhập nội dung trả lời.',

                'answer.max' =>
                    'Nội dung trả lời không được vượt quá 5.000 ký tự.',
            ]
        );

        DB::beginTransaction();

        try {
            DB::table(
                'product_question_answers'
            )->insert([
                'question_id' => $questionDetail->id,
                'user_id' => auth()->id(),
                'answer' => trim($validated['answer']),
                'is_official' => true,
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('product_questions')
                ->where('id', $questionDetail->id)
                ->update([
                    'status' => 'answered',
                    'is_public' => (bool) (
                        $validated['publish_question']
                        ?? true
                    ),
                    'answered_at' => now(),
                    'updated_at' => now(),
                ]);

            DB::commit();

            return back()->with(
                'success',
                'Đã gửi câu trả lời chính thức.'
            );
        } catch (Throwable $exception) {
            DB::rollBack();

            report($exception);

            return back()
                ->withInput()
                ->with(
                    'error',
                    app()->isLocal()
                        ? 'Lỗi trả lời câu hỏi: '
                            . $exception->getMessage()
                        : 'Không thể gửi câu trả lời.'
                );
        }
    }

    /**
     * Cập nhật câu trả lời.
     */
    public function updateAnswer(
        Request $request,
        int $question,
        int $answer
    ): RedirectResponse {
        $answerDetail = DB::table(
            'product_question_answers'
        )
            ->where('id', $answer)
            ->where('question_id', $question)
            ->whereNull('deleted_at')
            ->first([
                'id',
                'question_id',
                'user_id',
                'answer',
                'is_official',
                'status',
            ]);

        abort_if(! $answerDetail, 404);

        if (! $answerDetail->is_official) {
            return back()->with(
                'error',
                'Chỉ được sửa câu trả lời chính thức của cửa hàng.'
            );
        }

        $validated = $request->validate(
            [
                'answer' => [
                    'required',
                    'string',
                    'max:5000',
                ],

                'status' => [
                    'nullable',
                    'boolean',
                ],
            ],
            [
                'answer.required' =>
                    'Vui lòng nhập nội dung trả lời.',

                'answer.max' =>
                    'Nội dung trả lời không được vượt quá 5.000 ký tự.',
            ]
        );

        try {
            DB::table('product_question_answers')
                ->where('id', $answerDetail->id)
                ->update([
                    'answer' => trim($validated['answer']),
                    'status' => (bool) (
                        $validated['status'] ?? false
                    ),
                    'updated_at' => now(),
                ]);

            return back()->with(
                'success',
                'Cập nhật câu trả lời thành công.'
            );
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with(
                    'error',
                    app()->isLocal()
                        ? 'Lỗi cập nhật câu trả lời: '
                            . $exception->getMessage()
                        : 'Không thể cập nhật câu trả lời.'
                );
        }
    }

    /**
     * Xóa mềm câu trả lời chính thức.
     */
    public function destroyAnswer(
        int $question,
        int $answer
    ): RedirectResponse {
        $answerDetail = DB::table(
            'product_question_answers'
        )
            ->where('id', $answer)
            ->where('question_id', $question)
            ->whereNull('deleted_at')
            ->first([
                'id',
                'question_id',
                'user_id',
                'is_official',
            ]);

        abort_if(! $answerDetail, 404);

        if (! $answerDetail->is_official) {
            return back()->with(
                'error',
                'Không thể xóa câu trả lời của khách hàng tại khu vực quản trị.'
            );
        }

        DB::beginTransaction();

        try {
            DB::table('product_question_answers')
                ->where('id', $answerDetail->id)
                ->update([
                    'deleted_at' => now(),
                    'updated_at' => now(),
                ]);

            $hasActiveAnswer = DB::table(
                'product_question_answers'
            )
                ->where('question_id', $question)
                ->whereNull('deleted_at')
                ->where('status', true)
                ->exists();

            if (! $hasActiveAnswer) {
                DB::table('product_questions')
                    ->where('id', $question)
                    ->whereNull('deleted_at')
                    ->update([
                        'status' => 'published',
                        'answered_at' => null,
                        'updated_at' => now(),
                    ]);
            }

            DB::commit();

            return back()->with(
                'success',
                'Đã xóa câu trả lời chính thức.'
            );
        } catch (Throwable $exception) {
            DB::rollBack();

            report($exception);

            return back()->with(
                'error',
                app()->isLocal()
                    ? 'Lỗi xóa câu trả lời: '
                        . $exception->getMessage()
                    : 'Không thể xóa câu trả lời.'
            );
        }
    }

    private function questionStatuses(): array
    {
        return [
            'pending' => 'Chờ duyệt',
            'published' => 'Đã hiển thị',
            'answered' => 'Đã trả lời',
            'hidden' => 'Đã ẩn',
            'rejected' => 'Từ chối',
        ];
    }
}
