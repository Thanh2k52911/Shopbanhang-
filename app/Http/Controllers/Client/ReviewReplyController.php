<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreReviewReplyRequest;
use App\Models\ProductReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class ReviewReplyController extends Controller
{
    public function store(
        StoreReviewReplyRequest $request,
        ProductReview $review
    ): RedirectResponse {
        $userId = (int) $request->user()->id;

        if (! $review->status) {
            return back()->with(
                'error',
                'Đánh giá này hiện không còn hiển thị.'
            );
        }

        /*
         * Chỉ chính người đã viết review mới được tiếp tục
         * trao đổi với shop trong thread của review đó.
         */
        if ((int) $review->user_id !== $userId) {
            abort(403);
        }

        try {
            DB::transaction(function () use (
                $request,
                $review,
                $userId
            ): void {
                DB::table('review_replies')->insert([
                    'review_id' => $review->id,
                    'user_id' => $userId,
                    'content' => trim(
                        (string) $request->validated('content')
                    ),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }, 3);

            return back()
                ->withFragment('review-' . $review->id)
                ->with(
                    'success',
                    'Phản hồi của bạn đã được gửi tới cửa hàng.'
                );
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->withFragment('review-' . $review->id)
                ->with(
                    'error',
                    app()->isLocal()
                        ? 'Lỗi gửi phản hồi: '
                            . $exception->getMessage()
                        : 'Không thể gửi phản hồi lúc này.'
                );
        }
    }
}
