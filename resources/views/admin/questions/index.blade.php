@extends('admin.layouts.master')

@section('title', 'Quản lý hỏi đáp')
@section('page-title', 'Quản lý hỏi đáp')
@section('page-description', 'Theo dõi, duyệt và phản hồi câu hỏi sản phẩm.')

@section('content')
<div class="space-y-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Hỏi đáp sản phẩm</h2>
        <p class="mt-1 text-sm text-gray-500">Quản lý câu hỏi của thành viên và khách vãng lai.</p>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 2xl:grid-cols-8">
        @foreach ([
            ['Tổng câu hỏi', $statistics->total ?? 0, 'text-gray-900'],
            ['Chờ duyệt', $statistics->pending ?? 0, 'text-orange-600'],
            ['Đã hiển thị', $statistics->published ?? 0, 'text-blue-600'],
            ['Đã trả lời', $statistics->answered ?? 0, 'text-green-600'],
            ['Đã ẩn', $statistics->hidden ?? 0, 'text-gray-600'],
            ['Từ chối', $statistics->rejected ?? 0, 'text-red-600'],
            ['Công khai', $statistics->public_count ?? 0, 'text-pink-600'],
            ['Chưa trả lời', $answerStatistics['unanswered_questions'], 'text-purple-600'],
        ] as [$label, $value, $class])
            <article class="rounded-xl border border-gray-200 bg-white p-5">
                <p class="text-sm text-gray-500">{{ $label }}</p>
                <strong class="mt-2 block text-2xl {{ $class }}">{{ number_format((int) $value) }}</strong>
            </article>
        @endforeach
    </div>

    <section class="rounded-xl border border-gray-200 bg-white p-5">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <article class="rounded-lg bg-gray-50 p-4">
                <p class="text-sm text-gray-500">Tổng câu trả lời</p>
                <strong class="mt-1 block text-xl text-gray-900">{{ number_format((int) $answerStatistics['total_answers']) }}</strong>
            </article>

            <article class="rounded-lg bg-pink-50 p-4">
                <p class="text-sm text-pink-600">Câu trả lời chính thức đang hiển thị</p>
                <strong class="mt-1 block text-xl text-pink-700">{{ number_format((int) $answerStatistics['official_answers']) }}</strong>
            </article>
        </div>
    </section>

    <section class="rounded-xl border border-gray-200 bg-white p-5">
        <form method="GET" action="{{ route('admin.questions.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-7">
            <div class="xl:col-span-2">
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Tìm kiếm</label>
                <input
                    type="text"
                    name="keyword"
                    value="{{ request('keyword') }}"
                    placeholder="Sản phẩm, câu hỏi, tên hoặc email..."
                    class="w-full rounded-lg border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500"
                >
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Trạng thái</label>
                <select name="status" class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500">
                    <option value="">Tất cả</option>
                    @foreach ($questionStatuses as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Hiển thị</label>
                <select name="visibility" class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500">
                    <option value="">Tất cả</option>
                    <option value="public" @selected(request('visibility') === 'public')>Công khai</option>
                    <option value="private" @selected(request('visibility') === 'private')>Riêng tư</option>
                </select>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Trả lời</label>
                <select name="answered" class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500">
                    <option value="">Tất cả</option>
                    <option value="yes" @selected(request('answered') === 'yes')>Đã trả lời</option>
                    <option value="no" @selected(request('answered') === 'no')>Chưa trả lời</option>
                </select>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Người hỏi</label>
                <select name="author_type" class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500">
                    <option value="">Tất cả</option>
                    <option value="member" @selected(request('author_type') === 'member')>Thành viên</option>
                    <option value="guest" @selected(request('author_type') === 'guest')>Khách vãng lai</option>
                </select>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Sắp xếp</label>
                <select name="sort" class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500">
                    <option value="">Mới nhất</option>
                    <option value="oldest" @selected(request('sort') === 'oldest')>Cũ nhất</option>
                    <option value="answers_desc" @selected(request('sort') === 'answers_desc')>Nhiều câu trả lời</option>
                    <option value="unanswered_first" @selected(request('sort') === 'unanswered_first')>Chưa trả lời trước</option>
                    <option value="last_answer_desc" @selected(request('sort') === 'last_answer_desc')>Mới được trả lời</option>
                </select>
            </div>

            <div class="flex flex-wrap gap-3 md:col-span-2 xl:col-span-7">
                <button type="submit" class="rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white">Lọc dữ liệu</button>
                <a href="{{ route('admin.questions.index') }}" class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-700">Đặt lại</a>
            </div>
        </form>
    </section>

    <section class="overflow-hidden rounded-xl border border-gray-200 bg-white">
        <div class="border-b border-gray-200 px-5 py-4">
            <h3 class="text-lg font-bold text-gray-900">Danh sách câu hỏi</h3>
            <p class="mt-1 text-sm text-gray-500">Hiển thị {{ $questions->count() }} / {{ $questions->total() }} kết quả.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Người hỏi</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Sản phẩm</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Câu hỏi</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">Trả lời</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">Hiển thị</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">Trạng thái</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase text-gray-500">Thao tác</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @forelse ($questions as $question)
                        @php
                            $authorName = $question->user_id
                                ? ($question->user_name ?: 'Thành viên')
                                : ($question->guest_name ?: 'Khách vãng lai');

                            $authorEmail = $question->user_id
                                ? $question->user_email
                                : $question->guest_email;

                            $statusClass = match ($question->status) {
                                'pending' => 'bg-orange-100 text-orange-700',
                                'published' => 'bg-blue-100 text-blue-700',
                                'answered' => 'bg-green-100 text-green-700',
                                'hidden' => 'bg-gray-100 text-gray-700',
                                'rejected' => 'bg-red-100 text-red-700',
                                default => 'bg-gray-100 text-gray-700',
                            };
                        @endphp

                        <tr class="align-top hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <div class="min-w-[220px]">
                                    @if ($question->user_id)
                                        <a href="{{ route('admin.customers.show', $question->user_id) }}" class="font-semibold text-gray-900 hover:text-pink-600">{{ $authorName }}</a>
                                    @else
                                        <strong class="text-gray-900">{{ $authorName }}</strong>
                                    @endif

                                    <p class="mt-1 text-xs text-gray-500">{{ $authorEmail ?: 'Không có email' }}</p>
                                    <span class="mt-2 inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $question->user_id ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700' }}">
                                        {{ $question->user_id ? 'Thành viên' : 'Khách vãng lai' }}
                                    </span>
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                <div class="flex min-w-[250px] items-start gap-3">
                                    @if ($question->product_image)
                                        <img src="{{ asset('storage/' . ltrim($question->product_image, '/')) }}" alt="{{ $question->product_name }}" class="h-12 w-12 rounded-lg border border-gray-200 object-cover">
                                    @else
                                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-gray-100 text-xs text-gray-400">No image</div>
                                    @endif

                                    <a href="{{ route('admin.products.show', $question->product_id) }}" class="font-semibold text-gray-900 hover:text-pink-600">{{ $question->product_name }}</a>
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                <div class="min-w-[300px]">
                                    <p class="line-clamp-3 text-sm leading-6 text-gray-700">{{ $question->question }}</p>
                                    <p class="mt-2 text-xs text-gray-400">{{ \Carbon\Carbon::parse($question->created_at)->format('d/m/Y H:i') }}</p>
                                </div>
                            </td>

                            <td class="px-5 py-4 text-center text-sm text-gray-600">
                                <p class="font-semibold text-blue-600">{{ number_format((int) $question->answers_count) }}</p>
                                <p class="mt-1 text-xs text-gray-400">Chính thức: {{ number_format((int) $question->official_answers_count) }}</p>
                            </td>

                            <td class="px-5 py-4 text-center">
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $question->is_public ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                                    {{ $question->is_public ? 'Công khai' : 'Riêng tư' }}
                                </span>
                            </td>

                            <td class="px-5 py-4 text-center">
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">
                                    {{ $questionStatuses[$question->status] ?? $question->status }}
                                </span>
                            </td>

                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('admin.questions.show', $question->id) }}" class="inline-flex rounded-lg border border-gray-300 px-3 py-2 text-xs font-bold text-gray-700 hover:bg-gray-50">CHI TIẾT</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-16 text-center text-gray-500">Không tìm thấy câu hỏi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($questions->hasPages())
            <div class="border-t border-gray-200 px-5 py-4">{{ $questions->links() }}</div>
        @endif
    </section>
</div>
@endsection
