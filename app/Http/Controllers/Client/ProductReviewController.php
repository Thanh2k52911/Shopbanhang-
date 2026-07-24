<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductReviewRequest;
use App\Models\OrderItem;
use App\Models\ProductReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\ReviewLike;
use Illuminate\Http\JsonResponse;
use Throwable;
use App\Services\Admin\NotificationService;

class ProductReviewController extends Controller
{
    public function __construct(
        private readonly NotificationService $notificationService
    ) {
    }
    public function store(
        StoreProductReviewRequest $request
    ): RedirectResponse {
        $user = $request->user();

        /*
         * Khóa dòng order_items trong transaction để tránh
         * người dùng gửi form nhiều lần cùng lúc.
         */
        $uploadedPaths = [];
        $createdReview = null;

        try {
            DB::transaction(function () use (
                $request,
                $user,
                &$uploadedPaths,
                &$createdReview
            ): void {
                $orderItem = OrderItem::query()
                    ->with([
                        'order',
                        'product',
                    ])
                    ->lockForUpdate()
                    ->find($request->integer('order_item_id'));

                if (!$orderItem) {
                    throw new \RuntimeException(
                        'Sản phẩm trong đơn hàng không tồn tại.'
                    );
                }

                if (!$orderItem->order) {
                    throw new \RuntimeException(
                        'Không tìm thấy đơn hàng của sản phẩm.'
                    );
                }

                /*
                 * Chỉ chủ đơn hàng mới được đánh giá.
                 */
                if ((int) $orderItem->order->user_id !== (int) $user->id) {
                    throw new \RuntimeException(
                        'Bạn không có quyền đánh giá sản phẩm này.'
                    );
                }

                /*
                 * Chỉ đơn hoàn thành mới được đánh giá.
                 */
                if ($orderItem->order->order_status !== 'completed') {
                    throw new \RuntimeException(
                        'Bạn chỉ có thể đánh giá sản phẩm thuộc đơn hàng đã hoàn thành.'
                    );
                }

                /*
                 * product_id có thể null nếu sản phẩm bị xóa.
                 */
                if (
                    !$orderItem->product_id ||
                    !$orderItem->product
                ) {
                    throw new \RuntimeException(
                        'Sản phẩm này không còn tồn tại nên không thể đánh giá.'
                    );
                }

                if ($orderItem->is_reviewed) {
                    throw new \RuntimeException(
                        'Sản phẩm này đã được đánh giá.'
                    );
                }

                /*
                 * Schema hiện tại liên kết Review bằng:
                 * user_id + order_id + product_id.
                 *
                 * Không có order_item_id trong product_reviews.
                 */
                $reviewExists = ProductReview::query()
                    ->where('user_id', $user->id)
                    ->where('order_id', $orderItem->order_id)
                    ->where('product_id', $orderItem->product_id)
                    ->exists();

                if ($reviewExists) {
                    $orderItem->update([
                        'is_reviewed' => true,
                    ]);

                    throw new \RuntimeException(
                        'Bạn đã đánh giá sản phẩm này trong đơn hàng.'
                    );
                }

                $review = ProductReview::query()->create([
                    'product_id' => $orderItem->product_id,
                    'user_id' => $user->id,
                    'order_id' => $orderItem->order_id,
                    'rating' => $request->integer('rating'),
                    'content' => $request->filled('content')
                        ? trim((string) $request->input('content'))
                        : null,
                    'verified_purchase' => true,

                    /*
                     * true: review hiển thị ngay.
                     * Sau này làm Admin duyệt có thể đổi false.
                     */
                    'status' => true,
                ]);

                foreach ($request->file('images', []) as $image) {
                    $path = $image->store(
                        'reviews/images',
                        'public'
                    );

                    $uploadedPaths[] = $path;

                    $review->images()->create([
                        'image_path' => $path,
                    ]);
                }

                foreach ($request->file('videos', []) as $video) {
                    $path = $video->store(
                        'reviews/videos',
                        'public'
                    );

                    $uploadedPaths[] = $path;

                    $review->videos()->create([
                        'video_path' => $path,
                    ]);
                }

                $orderItem->update([
                    'is_reviewed' => true,
                ]);

                $createdReview = $review->fresh('product');
            });

            if ($createdReview) {
                $this->notificationService->safely(function () use ($createdReview): void {
                    $this->notificationService->notifyNewReview(
                        (int) $createdReview->id,
                        (string) ($createdReview->product?->name ?? 'Sản phẩm'),
                        (int) $createdReview->rating,
                        [
                            'user_id' => $createdReview->user_id,
                            'order_id' => $createdReview->order_id,
                        ]
                    );
                });
            }

            return back()->with(
                'success',
                'Đánh giá sản phẩm thành công.'
            );
        } catch (\RuntimeException $exception) {
            foreach ($uploadedPaths as $path) {
                Storage::disk('public')->delete($path);
            }

            return back()
                ->withInput()
                ->with('error', $exception->getMessage());
        } catch (Throwable $exception) {
            foreach ($uploadedPaths as $path) {
                Storage::disk('public')->delete($path);
            }

            report($exception);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Không thể gửi đánh giá lúc này. Vui lòng thử lại.'
                );

        }
    }
                /**
 * Thích hoặc bỏ thích một đánh giá.
 */
public function toggleLike(
    ProductReview $review
): JsonResponse {
    $user = request()->user();

    /*
     * Chỉ cho phép tương tác với đánh giá
     * đang được hiển thị trên website.
     */
    if (!$review->status) {
        return response()->json([
            'success' => false,
            'message' => 'Đánh giá này hiện không khả dụng.',
        ], 404);
    }

    $result = DB::transaction(function () use (
        $review,
        $user
    ): array {
        $existingLike = ReviewLike::query()
            ->where('review_id', $review->id)
            ->where('user_id', $user->id)
            ->lockForUpdate()
            ->first();

        if ($existingLike) {
            $existingLike->delete();

            $liked = false;
        } else {
            ReviewLike::query()->create([
                'review_id' => $review->id,
                'user_id' => $user->id,
            ]);

            $liked = true;
        }

        $likesCount = ReviewLike::query()
            ->where('review_id', $review->id)
            ->count();

        return [
            'liked' => $liked,
            'likes_count' => $likesCount,
        ];
    });

    return response()->json([
        'success' => true,
        'liked' => $result['liked'],
        'likes_count' => $result['likes_count'],
        'message' => $result['liked']
            ? 'Đã đánh dấu đánh giá là hữu ích.'
            : 'Đã bỏ đánh dấu hữu ích.',
    ]);
}
    }
