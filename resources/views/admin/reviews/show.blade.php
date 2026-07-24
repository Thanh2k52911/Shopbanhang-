@extends('admin.layouts.master')

@section('title', 'Chi tiết đánh giá')
@section('page-title', 'Chi tiết đánh giá')
@section('page-description', 'Xem nội dung, media, lượt hữu ích, phản hồi và trạng thái duyệt.')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Đánh giá #{{ $review->id }}</h2>
            <p class="mt-1 text-sm text-gray-500">{{ \Carbon\Carbon::parse($review->created_at)->format('d/m/Y H:i') }}</p>
        </div>

        <div class="flex gap-3">
            <a href="{{ route('admin.reviews.index') }}" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700">Quay lại</a>
            <a href="{{ route('admin.products.show', $review->product_id) }}" class="rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white">Xem sản phẩm</a>
        </div>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ session('error') }}</div>
    @endif

    @if ($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4">
            <ul class="list-disc space-y-1 pl-5 text-sm text-red-600">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="admin-responsive-sidebar-layout grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,2fr)_360px]">
        <div class="space-y-6">
            <section class="rounded-xl border border-gray-200 bg-white p-6">
                <div class="flex flex-col gap-5 md:flex-row md:items-start md:justify-between">
                    <div class="flex items-start gap-4">
                        @if ($review->customer_avatar)
                            <img src="{{ asset('storage/' . ltrim($review->customer_avatar, '/')) }}" alt="{{ $review->customer_name }}" class="h-14 w-14 rounded-full border border-gray-200 object-cover">
                        @else
                            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-pink-100 text-xl font-bold text-pink-600">
                                {{ mb_strtoupper(mb_substr($review->customer_name, 0, 1)) }}
                            </div>
                        @endif

                        <div>
                            <a href="{{ route('admin.customers.show', $review->user_id) }}" class="font-bold text-gray-900 hover:text-pink-600">{{ $review->customer_name }}</a>
                            <p class="mt-1 text-sm text-gray-500">{{ $review->customer_email }}</p>

                            <div class="mt-2 flex flex-wrap gap-2">
                                @if ($review->verified_purchase)
                                    <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">Mua hàng xác thực</span>
                                @endif

                                @if ($review->status)
                                    <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">Đã duyệt</span>
                                @else
                                    <span class="rounded-full bg-orange-100 px-3 py-1 text-xs font-semibold text-orange-700">Chờ duyệt</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="text-right">
                        <p class="text-2xl font-bold text-yellow-500">{{ str_repeat('★', (int) $review->rating) }}</p>
                        <p class="mt-1 text-sm text-gray-500">{{ $review->rating }}/5</p>
                    </div>
                </div>

                <div class="mt-6 border-t border-gray-200 pt-6">
                    <p class="whitespace-pre-line text-base leading-7 text-gray-700">{{ $review->content ?: 'Khách hàng không nhập nội dung đánh giá.' }}</p>
                </div>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-6">
                <h3 class="text-lg font-bold text-gray-900">Sản phẩm và đơn hàng</h3>

                <dl class="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div>
                        <dt class="text-sm text-gray-500">Sản phẩm</dt>
                        <dd class="mt-1">
                            <a href="{{ route('admin.products.show', $review->product_id) }}" class="font-semibold text-blue-600 hover:underline">{{ $review->product_name }}</a>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-gray-500">Trạng thái sản phẩm</dt>
                        <dd class="mt-1 font-semibold text-gray-900">{{ $review->product_status }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm text-gray-500">Đơn hàng</dt>
                        <dd class="mt-1">
                            @if ($review->order_id && $review->order_code)
                                <a href="{{ route('admin.orders.show', $review->order_id) }}" class="font-semibold text-blue-600 hover:underline">{{ $review->order_code }}</a>
                            @else
                                <span class="text-gray-500">Không có đơn liên kết</span>
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-gray-500">Trạng thái đơn</dt>
                        <dd class="mt-1 font-semibold text-gray-900">{{ $review->order_status ?: 'Không có' }}</dd>
                    </div>
                </dl>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-6">
                <h3 class="text-lg font-bold text-gray-900">Ảnh và video</h3>

                @if ($images->isEmpty() && $videos->isEmpty())
                    <p class="mt-4 text-sm text-gray-500">Đánh giá không có media.</p>
                @else
                    <div class="mt-5 grid grid-cols-2 gap-4 md:grid-cols-3 xl:grid-cols-4">
                        @foreach ($images as $image)
                            <a href="{{ asset('storage/' . ltrim($image->image_path, '/')) }}" target="_blank" class="overflow-hidden rounded-xl border border-gray-200">
                                <img src="{{ asset('storage/' . ltrim($image->image_path, '/')) }}" alt="Review image" class="h-40 w-full object-cover">
                            </a>
                        @endforeach

                        @foreach ($videos as $video)
                            <div class="overflow-hidden rounded-xl border border-gray-200">
                                <video controls class="h-40 w-full bg-black object-contain">
                                    <source src="{{ asset('storage/' . ltrim($video->video_path, '/')) }}">
                                </video>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-6">
                <h3 class="text-lg font-bold text-gray-900">Phản hồi</h3>

                <div class="mt-5 space-y-4">
                    @forelse ($replies as $reply)
                        @php
                            $isAdminReply = $reply->roles->intersect([
                                'super_admin',
                                'admin',
                                'staff',
                                'customer_support',
                            ])->isNotEmpty();
                        @endphp

                        <article class="rounded-xl border {{ $isAdminReply ? 'border-pink-200 bg-pink-50' : 'border-gray-200 bg-white' }} p-4">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <strong class="text-gray-900">{{ $reply->user_name }}</strong>

                                        @if ($isAdminReply)
                                            <span class="rounded-full bg-pink-100 px-2.5 py-1 text-xs font-semibold text-pink-700">Quản trị</span>
                                        @endif
                                    </div>

                                    <p class="mt-1 text-xs text-gray-500">{{ $reply->user_email }} · {{ \Carbon\Carbon::parse($reply->created_at)->format('d/m/Y H:i') }}</p>
                                </div>

                                @if ($isAdminReply)
                                    <form method="POST" action="{{ route('admin.reviews.replies.destroy', [$review->id, $reply->id]) }}" onsubmit="return confirm('Xóa phản hồi này?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-semibold text-red-600 hover:underline">Xóa</button>
                                    </form>
                                @endif
                            </div>

                            <p class="mt-3 whitespace-pre-line text-sm leading-6 text-gray-700">{{ $reply->content }}</p>
                        </article>
                    @empty
                        <p class="text-sm text-gray-500">Chưa có phản hồi.</p>
                    @endforelse
                </div>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-6">
                <h3 class="text-lg font-bold text-gray-900">Đánh giá khác của sản phẩm</h3>

                <div class="mt-5 space-y-3">
                    @forelse ($relatedReviews as $related)
                        <a href="{{ route('admin.reviews.show', $related->id) }}" class="block rounded-lg border border-gray-200 p-4 hover:bg-gray-50">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <strong class="text-gray-900">{{ $related->customer_name }}</strong>
                                    <p class="mt-1 text-sm text-yellow-500">{{ str_repeat('★', (int) $related->rating) }}</p>
                                </div>

                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $related->status ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700' }}">
                                    {{ $related->status ? 'Đã duyệt' : 'Chờ duyệt' }}
                                </span>
                            </div>

                            <p class="mt-3 line-clamp-2 text-sm text-gray-600">{{ $related->content ?: 'Không có nội dung.' }}</p>
                        </a>
                    @empty
                        <p class="text-sm text-gray-500">Không có đánh giá liên quan.</p>
                    @endforelse
                </div>
            </section>
        </div>

        <aside class="space-y-6">
            <section class="rounded-xl border border-gray-200 bg-white p-5">
                <h3 class="text-lg font-bold text-gray-900">Trạng thái đánh giá</h3>

                <form method="POST" action="{{ route('admin.reviews.update-status', $review->id) }}" class="mt-4 space-y-4">
                    @csrf
                    @method('PATCH')

                    <select name="status" class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500">
                        <option value="1" @selected((bool) $review->status === true)>Duyệt và hiển thị</option>
                        <option value="0" @selected((bool) $review->status === false)>Ẩn / chờ duyệt</option>
                    </select>

                    <button type="submit" class="w-full rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white">Cập nhật trạng thái</button>
                </form>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-5">
                <h3 class="text-lg font-bold text-gray-900">Phản hồi đánh giá</h3>

                <form method="POST" action="{{ route('admin.reviews.reply', $review->id) }}" class="mt-4 space-y-4">
                    @csrf

                    <textarea
                        name="content"
                        rows="6"
                        maxlength="5000"
                        required
                        placeholder="Nhập nội dung phản hồi chính thức..."
                        class="w-full rounded-lg border-gray-300 px-4 py-3 text-sm focus:border-pink-500 focus:ring-pink-500"
                    >{{ old('content') }}</textarea>

                    <button type="submit" class="w-full rounded-lg bg-pink-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-pink-700">Gửi phản hồi</button>
                </form>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-5">
                <h3 class="text-lg font-bold text-gray-900">Lượt hữu ích</h3>

                <p class="mt-3 text-2xl font-bold text-blue-600">{{ number_format($likes->count()) }}</p>

                <div class="mt-4 max-h-72 space-y-3 overflow-y-auto">
                    @forelse ($likes as $like)
                        <div class="rounded-lg border border-gray-200 p-3">
                            <strong class="text-sm text-gray-900">{{ $like->user_name }}</strong>
                            <p class="mt-1 text-xs text-gray-500">{{ $like->user_email }}</p>
                            <p class="mt-1 text-xs text-gray-400">{{ \Carbon\Carbon::parse($like->created_at)->format('d/m/Y H:i') }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">Chưa có lượt hữu ích.</p>
                    @endforelse
                </div>
            </section>
        </aside>
    </div>
</div>
@endsection
