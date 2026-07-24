<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class ReviewController extends Controller
{
    /**
     * Danh sách đánh giá sản phẩm.
     */
    public function index(Request $request): View
    {
        $query = DB::table('product_reviews as pr')
            ->join('products as p', 'pr.product_id', '=', 'p.id')
            ->join('users as u', 'pr.user_id', '=', 'u.id')
            ->leftJoin('orders as o', 'pr.order_id', '=', 'o.id')
            ->whereNull('p.deleted_at')
            ->whereNull('u.deleted_at')
            ->select([
                'pr.id',
                'pr.product_id',
                'pr.user_id',
                'pr.order_id',
                'pr.rating',
                'pr.content',
                'pr.verified_purchase',
                'pr.status',
                'pr.created_at',
                'pr.updated_at',

                'p.name as product_name',
                'p.slug as product_slug',

                'u.name as customer_name',
                'u.email as customer_email',
                'u.avatar as customer_avatar',
                'u.status as customer_status',

                'o.order_code',
            ])
            ->selectSub(
                function (Builder $builder): void {
                    $builder
                        ->from('review_images')
                        ->selectRaw('COUNT(*)')
                        ->whereColumn(
                            'review_images.review_id',
                            'pr.id'
                        );
                },
                'images_count'
            )
            ->selectSub(
                function (Builder $builder): void {
                    $builder
                        ->from('review_videos')
                        ->selectRaw('COUNT(*)')
                        ->whereColumn(
                            'review_videos.review_id',
                            'pr.id'
                        );
                },
                'videos_count'
            )
            ->selectSub(
                function (Builder $builder): void {
                    $builder
                        ->from('review_likes')
                        ->selectRaw('COUNT(*)')
                        ->whereColumn(
                            'review_likes.review_id',
                            'pr.id'
                        );
                },
                'likes_count'
            )
            ->selectSub(
                function (Builder $builder): void {
                    $builder
                        ->from('review_replies')
                        ->selectRaw('COUNT(*)')
                        ->whereColumn(
                            'review_replies.review_id',
                            'pr.id'
                        );
                },
                'replies_count'
            )
            ->selectSub(
                function (Builder $builder): void {
                    $builder
                        ->from('product_images')
                        ->select('image_path')
                        ->whereColumn(
                            'product_images.product_id',
                            'pr.product_id'
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
                            'pr.content',
                            'like',
                            '%' . $keyword . '%'
                        )
                        ->orWhere(
                            'o.order_code',
                            'like',
                            '%' . $keyword . '%'
                        );
                }
            );
        }

        if ($request->filled('rating')) {
            $query->where(
                'pr.rating',
                (int) $request->input('rating')
            );
        }

        match ($request->input('status')) {
            'approved' => $query->where('pr.status', true),
            'pending' => $query->where('pr.status', false),
            default => null,
        };

        match ($request->input('verified_purchase')) {
            'yes' => $query->where('pr.verified_purchase', true),
            'no' => $query->where('pr.verified_purchase', false),
            default => null,
        };

        match ($request->input('media')) {
            'with_media' => $query->where(
                function (Builder $builder): void {
                    $builder
                        ->whereExists(
                            function (Builder $mediaQuery): void {
                                $mediaQuery
                                    ->selectRaw('1')
                                    ->from('review_images')
                                    ->whereColumn(
                                        'review_images.review_id',
                                        'pr.id'
                                    );
                            }
                        )
                        ->orWhereExists(
                            function (Builder $mediaQuery): void {
                                $mediaQuery
                                    ->selectRaw('1')
                                    ->from('review_videos')
                                    ->whereColumn(
                                        'review_videos.review_id',
                                        'pr.id'
                                    );
                            }
                        );
                }
            ),

            'without_media' => $query
                ->whereNotExists(
                    function (Builder $builder): void {
                        $builder
                            ->selectRaw('1')
                            ->from('review_images')
                            ->whereColumn(
                                'review_images.review_id',
                                'pr.id'
                            );
                    }
                )
                ->whereNotExists(
                    function (Builder $builder): void {
                        $builder
                            ->selectRaw('1')
                            ->from('review_videos')
                            ->whereColumn(
                                'review_videos.review_id',
                                'pr.id'
                            );
                    }
                ),

            default => null,
        };

        match ($request->input('reply')) {
            'replied' => $query->whereExists(
                function (Builder $builder): void {
                    $builder
                        ->selectRaw('1')
                        ->from('review_replies')
                        ->whereColumn(
                            'review_replies.review_id',
                            'pr.id'
                        );
                }
            ),

            'unreplied' => $query->whereNotExists(
                function (Builder $builder): void {
                    $builder
                        ->selectRaw('1')
                        ->from('review_replies')
                        ->whereColumn(
                            'review_replies.review_id',
                            'pr.id'
                        );
                }
            ),

            default => null,
        };

        match ($request->input('sort')) {
            'oldest' => $query->orderBy('pr.created_at'),
            'rating_desc' => $query
                ->orderByDesc('pr.rating')
                ->orderByDesc('pr.created_at'),
            'rating_asc' => $query
                ->orderBy('pr.rating')
                ->orderByDesc('pr.created_at'),
            'likes_desc' => $query
                ->orderByDesc('likes_count')
                ->orderByDesc('pr.created_at'),
            'replies_desc' => $query
                ->orderByDesc('replies_count')
                ->orderByDesc('pr.created_at'),
            default => $query->orderByDesc('pr.created_at'),
        };

        $reviews = $query
            ->paginate(20)
            ->withQueryString();

        $statistics = DB::table('product_reviews')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END), 0) as approved'
            )
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END), 0) as pending'
            )
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN verified_purchase = 1 THEN 1 ELSE 0 END), 0) as verified_purchase'
            )
            ->selectRaw(
                'COALESCE(AVG(rating), 0) as average_rating'
            )
            ->first();

        $ratingCounts = DB::table('product_reviews')
            ->selectRaw('rating, COUNT(*) as total')
            ->groupBy('rating')
            ->pluck('total', 'rating');

        $ratingBreakdown = collect(range(5, 1))
            ->mapWithKeys(
                function (int $rating) use ($ratingCounts): array {
                    return [
                        $rating => (int) (
                            $ratingCounts[$rating] ?? 0
                        ),
                    ];
                }
            );

        $mediaStatistics = [
            'with_images' => DB::table('product_reviews as pr')
                ->whereExists(
                    function (Builder $builder): void {
                        $builder
                            ->selectRaw('1')
                            ->from('review_images')
                            ->whereColumn(
                                'review_images.review_id',
                                'pr.id'
                            );
                    }
                )
                ->count(),

            'with_videos' => DB::table('product_reviews as pr')
                ->whereExists(
                    function (Builder $builder): void {
                        $builder
                            ->selectRaw('1')
                            ->from('review_videos')
                            ->whereColumn(
                                'review_videos.review_id',
                                'pr.id'
                            );
                    }
                )
                ->count(),

            'unreplied' => DB::table('product_reviews as pr')
                ->whereNotExists(
                    function (Builder $builder): void {
                        $builder
                            ->selectRaw('1')
                            ->from('review_replies')
                            ->whereColumn(
                                'review_replies.review_id',
                                'pr.id'
                            );
                    }
                )
                ->count(),
        ];

        return view('admin.reviews.index', [
            'reviews' => $reviews,
            'statistics' => $statistics,
            'ratingBreakdown' => $ratingBreakdown,
            'mediaStatistics' => $mediaStatistics,
        ]);
    }

    /**
     * Chi tiết đánh giá.
     */
    public function show(int $review): View
    {
        $reviewDetail = DB::table('product_reviews as pr')
            ->join('products as p', 'pr.product_id', '=', 'p.id')
            ->join('users as u', 'pr.user_id', '=', 'u.id')
            ->leftJoin('orders as o', 'pr.order_id', '=', 'o.id')
            ->where('pr.id', $review)
            ->whereNull('p.deleted_at')
            ->whereNull('u.deleted_at')
            ->select([
                'pr.id',
                'pr.product_id',
                'pr.user_id',
                'pr.order_id',
                'pr.rating',
                'pr.content',
                'pr.verified_purchase',
                'pr.status',
                'pr.created_at',
                'pr.updated_at',

                'p.name as product_name',
                'p.slug as product_slug',
                'p.status as product_status',

                'u.name as customer_name',
                'u.email as customer_email',
                'u.avatar as customer_avatar',
                'u.status as customer_status',
                'u.last_login_at',

                'o.order_code',
                'o.order_status',
                'o.payment_status',
                'o.shipping_status',
            ])
            ->first();

        abort_if(! $reviewDetail, 404);

        $images = DB::table('review_images')
            ->where('review_id', $reviewDetail->id)
            ->orderBy('id')
            ->get([
                'id',
                'image_path',
                'created_at',
            ]);

        $videos = DB::table('review_videos')
            ->where('review_id', $reviewDetail->id)
            ->orderBy('id')
            ->get([
                'id',
                'video_path',
                'created_at',
            ]);

        $likes = DB::table('review_likes as rl')
            ->join('users as u', 'rl.user_id', '=', 'u.id')
            ->where('rl.review_id', $reviewDetail->id)
            ->whereNull('u.deleted_at')
            ->orderByDesc('rl.created_at')
            ->get([
                'rl.id',
                'rl.user_id',
                'rl.created_at',
                'u.name as user_name',
                'u.email as user_email',
                'u.avatar as user_avatar',
            ]);

        $replies = DB::table('review_replies as rr')
            ->join('users as u', 'rr.user_id', '=', 'u.id')
            ->where('rr.review_id', $reviewDetail->id)
            ->whereNull('u.deleted_at')
            ->orderBy('rr.created_at')
            ->get([
                'rr.id',
                'rr.user_id',
                'rr.content',
                'rr.created_at',
                'rr.updated_at',

                'u.name as user_name',
                'u.email as user_email',
                'u.avatar as user_avatar',
            ])
            ->map(function ($reply) {
                $reply->roles = DB::table('user_roles as ur')
                    ->join('roles as r', 'ur.role_id', '=', 'r.id')
                    ->where('ur.user_id', $reply->user_id)
                    ->pluck('r.name');

                return $reply;
            });

        $relatedReviews = DB::table('product_reviews as pr')
            ->join('users as u', 'pr.user_id', '=', 'u.id')
            ->where('pr.product_id', $reviewDetail->product_id)
            ->where('pr.id', '!=', $reviewDetail->id)
            ->orderByDesc('pr.created_at')
            ->limit(5)
            ->get([
                'pr.id',
                'pr.rating',
                'pr.content',
                'pr.status',
                'pr.created_at',
                'u.name as customer_name',
            ]);

        return view('admin.reviews.show', [
            'review' => $reviewDetail,
            'images' => $images,
            'videos' => $videos,
            'likes' => $likes,
            'replies' => $replies,
            'relatedReviews' => $relatedReviews,
        ]);
    }

    /**
     * Duyệt hoặc ẩn đánh giá.
     */
    public function updateStatus(
        Request $request,
        int $review
    ): RedirectResponse {
        $reviewDetail = DB::table('product_reviews')
            ->where('id', $review)
            ->first([
                'id',
                'product_id',
                'user_id',
                'status',
                'rating',
                'content',
            ]);

        abort_if(! $reviewDetail, 404);

        $validated = $request->validate(
            [
                'status' => [
                    'required',
                    'boolean',
                ],
            ],
            [
                'status.required' =>
                    'Vui lòng chọn trạng thái đánh giá.',

                'status.boolean' =>
                    'Trạng thái đánh giá không hợp lệ.',
            ]
        );

        $newStatus = (bool) $validated['status'];

        if ((bool) $reviewDetail->status === $newStatus) {
            return back()->with(
                'error',
                $newStatus
                    ? 'Đánh giá đã được duyệt.'
                    : 'Đánh giá đang ở trạng thái ẩn.'
            );
        }

        DB::beginTransaction();

        try {
            DB::table('product_reviews')
                ->where('id', $reviewDetail->id)
                ->update([
                    'status' => $newStatus,
                    'updated_at' => now(),
                ]);

            DB::commit();

            return back()->with(
                'success',
                $newStatus
                    ? 'Đã duyệt và hiển thị đánh giá.'
                    : 'Đã ẩn đánh giá khỏi trang sản phẩm.'
            );
        } catch (Throwable $exception) {
            DB::rollBack();

            report($exception);

            return back()->with(
                'error',
                app()->isLocal()
                    ? 'Lỗi cập nhật đánh giá: '
                        . $exception->getMessage()
                    : 'Không thể cập nhật trạng thái đánh giá.'
            );
        }
    }

    /**
     * Admin trả lời đánh giá.
     */
    public function reply(
        Request $request,
        int $review
    ): RedirectResponse {
        $reviewDetail = DB::table('product_reviews')
            ->where('id', $review)
            ->first([
                'id',
                'product_id',
                'user_id',
            ]);

        abort_if(! $reviewDetail, 404);

        $validated = $request->validate(
            [
                'content' => [
                    'required',
                    'string',
                    'max:5000',
                ],
            ],
            [
                'content.required' =>
                    'Vui lòng nhập nội dung phản hồi.',

                'content.max' =>
                    'Nội dung phản hồi không được vượt quá 5.000 ký tự.',
            ]
        );

        try {
            DB::table('review_replies')->insert([
                'review_id' => $reviewDetail->id,
                'user_id' => auth()->id(),
                'content' => trim($validated['content']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return back()->with(
                'success',
                'Đã gửi phản hồi cho đánh giá.'
            );
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with(
                    'error',
                    app()->isLocal()
                        ? 'Lỗi gửi phản hồi: '
                            . $exception->getMessage()
                        : 'Không thể gửi phản hồi.'
                );
        }
    }

    /**
     * Xóa câu trả lời do tài khoản quản trị tạo.
     */
    public function destroyReply(
        int $review,
        int $reply
    ): RedirectResponse {
        $replyDetail = DB::table('review_replies')
            ->where('id', $reply)
            ->where('review_id', $review)
            ->first([
                'id',
                'review_id',
                'user_id',
                'content',
            ]);

        abort_if(! $replyDetail, 404);

        $isAdminReply = DB::table('user_roles as ur')
            ->join('roles as r', 'ur.role_id', '=', 'r.id')
            ->where('ur.user_id', $replyDetail->user_id)
            ->whereIn(
                'r.name',
                [
                    'super_admin',
                    'admin',
                    'staff',
                    'customer_support',
                ]
            )
            ->exists();

        if (! $isAdminReply) {
            return back()->with(
                'error',
                'Không thể xóa phản hồi của khách hàng tại khu vực quản trị.'
            );
        }

        try {
            DB::table('review_replies')
                ->where('id', $replyDetail->id)
                ->delete();

            return back()->with(
                'success',
                'Đã xóa phản hồi quản trị.'
            );
        } catch (Throwable $exception) {
            report($exception);

            return back()->with(
                'error',
                app()->isLocal()
                    ? 'Lỗi xóa phản hồi: '
                        . $exception->getMessage()
                    : 'Không thể xóa phản hồi.'
            );
        }
    }
}
