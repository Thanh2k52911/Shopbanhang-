@extends('admin.layouts.master')

@section('title', 'Quản lý đánh giá')
@section('page-title', 'Quản lý đánh giá')
@section('page-description', 'Theo dõi, duyệt và phản hồi đánh giá sản phẩm.')

@section('content')
<div class="space-y-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Đánh giá sản phẩm</h2>
        <p class="mt-1 text-sm text-gray-500">Quản lý đánh giá, media, lượt hữu ích và phản hồi từ khách hàng.</p>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 2xl:grid-cols-8">
        @foreach ([
            ['Tổng đánh giá', $statistics->total ?? 0, 'text-gray-900'],
            ['Đã duyệt', $statistics->approved ?? 0, 'text-green-600'],
            ['Chờ duyệt', $statistics->pending ?? 0, 'text-orange-600'],
            ['Mua hàng xác thực', $statistics->verified_purchase ?? 0, 'text-blue-600'],
            ['Có ảnh', $mediaStatistics['with_images'], 'text-pink-600'],
            ['Có video', $mediaStatistics['with_videos'], 'text-purple-600'],
            ['Chưa phản hồi', $mediaStatistics['unreplied'], 'text-red-600'],
        ] as [$label, $value, $class])
            <article class="rounded-xl border border-gray-200 bg-white p-5">
                <p class="text-sm text-gray-500">{{ $label }}</p>
                <strong class="mt-2 block text-2xl {{ $class }}">{{ number_format((int) $value) }}</strong>
            </article>
        @endforeach

        <article class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">Điểm trung bình</p>
            <strong class="mt-2 block text-2xl text-yellow-500">{{ number_format((float) ($statistics->average_rating ?? 0), 2) }}★</strong>
        </article>
    </div>

    <section class="rounded-xl border border-gray-200 bg-white p-5">
        <h3 class="text-lg font-bold text-gray-900">Phân bố số sao</h3>

        <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-5">
            @foreach ($ratingBreakdown as $rating => $count)
                <div class="rounded-lg bg-gray-50 p-4 text-center">
                    <p class="text-sm font-semibold text-yellow-500">{{ $rating }}★</p>
                    <strong class="mt-1 block text-xl text-gray-900">{{ number_format((int) $count) }}</strong>
                </div>
            @endforeach
        </div>
    </section>

    <section class="rounded-xl border border-gray-200 bg-white p-5">
        <form method="GET" action="{{ route('admin.reviews.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-7">
            <div class="xl:col-span-2">
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Tìm kiếm</label>
                <input
                    type="text"
                    name="keyword"
                    value="{{ request('keyword') }}"
                    placeholder="Sản phẩm, khách hàng, email, nội dung, mã đơn..."
                    class="w-full rounded-lg border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                >
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Số sao</label>
                <select name="rating" class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500">
                    <option value="">Tất cả</option>
                    @for ($rating = 5; $rating >= 1; $rating--)
                        <option value="{{ $rating }}" @selected((string) request('rating') === (string) $rating)>{{ $rating }} sao</option>
                    @endfor
                </select>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Trạng thái</label>
                <select name="status" class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500">
                    <option value="">Tất cả</option>
                    <option value="approved" @selected(request('status') === 'approved')>Đã duyệt</option>
                    <option value="pending" @selected(request('status') === 'pending')>Chờ duyệt</option>
                </select>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Mua hàng xác thực</label>
                <select name="verified_purchase" class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500">
                    <option value="">Tất cả</option>
                    <option value="yes" @selected(request('verified_purchase') === 'yes')>Có</option>
                    <option value="no" @selected(request('verified_purchase') === 'no')>Không</option>
                </select>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Media</label>
                <select name="media" class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500">
                    <option value="">Tất cả</option>
                    <option value="with_media" @selected(request('media') === 'with_media')>Có ảnh/video</option>
                    <option value="without_media" @selected(request('media') === 'without_media')>Không có media</option>
                </select>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Phản hồi</label>
                <select name="reply" class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500">
                    <option value="">Tất cả</option>
                    <option value="replied" @selected(request('reply') === 'replied')>Đã phản hồi</option>
                    <option value="unreplied" @selected(request('reply') === 'unreplied')>Chưa phản hồi</option>
                </select>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Sắp xếp</label>
                <select name="sort" class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500">
                    <option value="">Mới nhất</option>
                    <option value="oldest" @selected(request('sort') === 'oldest')>Cũ nhất</option>
                    <option value="rating_desc" @selected(request('sort') === 'rating_desc')>Sao cao nhất</option>
                    <option value="rating_asc" @selected(request('sort') === 'rating_asc')>Sao thấp nhất</option>
                    <option value="likes_desc" @selected(request('sort') === 'likes_desc')>Nhiều lượt hữu ích</option>
                    <option value="replies_desc" @selected(request('sort') === 'replies_desc')>Nhiều phản hồi</option>
                </select>
            </div>

            <div class="flex flex-wrap gap-3 md:col-span-2 xl:col-span-7">
                <button type="submit" class="rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white">Lọc dữ liệu</button>
                <a href="{{ route('admin.reviews.index') }}" class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-700">Đặt lại</a>
            </div>
        </form>
    </section>

    <section class="overflow-hidden rounded-xl border border-gray-200 bg-white">
        <div class="border-b border-gray-200 px-5 py-4">
            <h3 class="text-lg font-bold text-gray-900">Danh sách đánh giá</h3>
            <p class="mt-1 text-sm text-gray-500">Hiển thị {{ $reviews->count() }} / {{ $reviews->total() }} kết quả.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Khách hàng</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Sản phẩm</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Đánh giá</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">Media</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">Tương tác</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">Trạng thái</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase text-gray-500">Thao tác</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @forelse ($reviews as $review)
                        <tr class="align-top hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <div class="min-w-[220px]">
                                    <a href="{{ route('admin.customers.show', $review->user_id) }}" class="font-semibold text-gray-900 hover:text-pink-600">
                                        {{ $review->customer_name }}
                                    </a>
                                    <p class="mt-1 text-xs text-gray-500">{{ $review->customer_email }}</p>
                                    @if ($review->verified_purchase)
                                        <span class="mt-2 inline-flex rounded-full bg-blue-100 px-2.5 py-1 text-xs font-semibold text-blue-700">Đã mua hàng</span>
                                    @endif
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                <div class="flex min-w-[260px] items-start gap-3">
                                    @if ($review->product_image)
                                        <img src="{{ asset('storage/' . ltrim($review->product_image, '/')) }}" alt="{{ $review->product_name }}" class="h-12 w-12 rounded-lg border border-gray-200 object-cover">
                                    @else
                                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-gray-100 text-xs text-gray-400">No image</div>
                                    @endif

                                    <div>
                                        <a href="{{ route('admin.products.show', $review->product_id) }}" class="font-semibold text-gray-900 hover:text-pink-600">
                                            {{ $review->product_name }}
                                        </a>

                                        @if ($review->order_id && $review->order_code)
                                            <p class="mt-1 text-xs text-gray-500">
                                                Đơn:
                                                <a href="{{ route('admin.orders.show', $review->order_id) }}" class="font-semibold text-blue-600 hover:underline">{{ $review->order_code }}</a>
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                <div class="min-w-[280px]">
                                    <p class="font-semibold text-yellow-500">
                                        {{ str_repeat('★', (int) $review->rating) }}
                                        <span class="text-gray-300">{{ str_repeat('★', 5 - (int) $review->rating) }}</span>
                                    </p>
                                    <p class="mt-2 line-clamp-3 text-sm leading-6 text-gray-600">{{ $review->content ?: 'Không có nội dung.' }}</p>
                                    <p class="mt-2 text-xs text-gray-400">{{ \Carbon\Carbon::parse($review->created_at)->format('d/m/Y H:i') }}</p>
                                </div>
                            </td>

                            <td class="px-5 py-4 text-center text-sm text-gray-600">
                                <p>{{ number_format((int) $review->images_count) }} ảnh</p>
                                <p class="mt-1">{{ number_format((int) $review->videos_count) }} video</p>
                            </td>

                            <td class="px-5 py-4 text-center text-sm text-gray-600">
                                <p>{{ number_format((int) $review->likes_count) }} hữu ích</p>
                                <p class="mt-1">{{ number_format((int) $review->replies_count) }} phản hồi</p>
                            </td>

                            <td class="px-5 py-4 text-center">
                                @if ($review->status)
                                    <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">Đã duyệt</span>
                                @else
                                    <span class="rounded-full bg-orange-100 px-3 py-1 text-xs font-semibold text-orange-700">Chờ duyệt</span>
                                @endif
                            </td>

                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('admin.reviews.show', $review->id) }}" class="inline-flex rounded-lg border border-gray-300 px-3 py-2 text-xs font-bold text-gray-700 hover:bg-gray-50">CHI TIẾT</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-16 text-center text-gray-500">Không tìm thấy đánh giá.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($reviews->hasPages())
            <div class="border-t border-gray-200 px-5 py-4">{{ $reviews->links() }}</div>
        @endif
    </section>
</div>
@endsection
