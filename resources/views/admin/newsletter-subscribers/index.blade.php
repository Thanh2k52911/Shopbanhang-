@extends('admin.layouts.master')

@section('title', 'Quản lý Newsletter')
@section('page-title', 'Quản lý Newsletter')
@section('page-description', 'Theo dõi người đăng ký nhận tin và trạng thái xác minh email.')

@section('content')
<div class="space-y-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Newsletter</h2>
        <p class="mt-1 text-sm text-gray-500">Quản lý danh sách email đăng ký nhận tin từ hệ thống.</p>
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

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
        @foreach ([
            ['Tổng đăng ký', $statistics->total ?? 0, 'text-gray-900'],
            ['Đang hoạt động', $statistics->active ?? 0, 'text-green-600'],
            ['Đã hủy', $statistics->inactive ?? 0, 'text-red-600'],
            ['Đã xác minh', $statistics->verified ?? 0, 'text-blue-600'],
            ['Chưa xác minh', $statistics->unverified ?? 0, 'text-orange-600'],
            ['Thành viên / Khách', ($statistics->members ?? 0) . ' / ' . ($statistics->guests ?? 0), 'text-pink-600'],
        ] as [$label, $value, $class])
            <article class="rounded-xl border border-gray-200 bg-white p-5">
                <p class="text-sm text-gray-500">{{ $label }}</p>
                <strong class="mt-2 block text-2xl {{ $class }}">
                    {{ is_numeric($value) ? number_format((int) $value) : $value }}
                </strong>
            </article>
        @endforeach
    </div>

    <section class="rounded-xl border border-gray-200 bg-white p-5">
        <h3 class="text-lg font-bold text-gray-900">Thêm người đăng ký thủ công</h3>

        <form method="POST" action="{{ route('admin.newsletter-subscribers.store') }}" class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
            @csrf

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Email *</label>
                <input type="email" name="email" value="{{ old('email') }}" required class="w-full rounded-lg border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500">
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Tên</label>
                <input type="text" name="name" value="{{ old('name') }}" maxlength="150" class="w-full rounded-lg border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500">
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Nguồn đăng ký</label>
                <input type="text" name="source" value="{{ old('source', 'admin') }}" maxlength="50" class="w-full rounded-lg border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500">
            </div>

            <label class="flex items-start gap-3 rounded-lg border border-gray-200 p-3">
                <input type="hidden" name="verified" value="0">
                <input type="checkbox" name="verified" value="1" @checked(old('verified')) class="mt-0.5 rounded border-gray-300 text-pink-600 focus:ring-pink-500">
                <span>
                    <strong class="block text-sm text-gray-900">Đã xác minh</strong>
                    <span class="mt-1 block text-xs text-gray-500">Đánh dấu email là đã xác minh.</span>
                </span>
            </label>

            <div class="flex items-end">
                <button type="submit" class="w-full rounded-lg bg-pink-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-pink-700">Thêm đăng ký</button>
            </div>
        </form>
    </section>

    <section class="rounded-xl border border-gray-200 bg-white p-5">
        <form method="GET" action="{{ route('admin.newsletter-subscribers.index') }}" class="newsletter-admin-form-grid grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-6">
            <div class="xl:col-span-2">
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Tìm kiếm</label>
                <input type="text" name="keyword" value="{{ request('keyword') }}" placeholder="Email, tên hoặc nguồn..." class="w-full rounded-lg border-gray-300 px-4 py-2.5 text-sm focus:border-pink-500 focus:ring-pink-500">
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Trạng thái</label>
                <select name="status" class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500">
                    <option value="">Tất cả</option>
                    <option value="active" @selected(request('status') === 'active')>Đang hoạt động</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Đã hủy</option>
                </select>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Xác minh</label>
                <select name="verification" class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500">
                    <option value="">Tất cả</option>
                    <option value="verified" @selected(request('verification') === 'verified')>Đã xác minh</option>
                    <option value="unverified" @selected(request('verification') === 'unverified')>Chưa xác minh</option>
                </select>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Loại người dùng</label>
                <select name="member_type" class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500">
                    <option value="">Tất cả</option>
                    <option value="member" @selected(request('member_type') === 'member')>Thành viên</option>
                    <option value="guest" @selected(request('member_type') === 'guest')>Khách</option>
                </select>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Nguồn</label>
                <select name="source" class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500">
                    <option value="">Tất cả</option>
                    @foreach ($sources as $source)
                        <option value="{{ $source }}" @selected(request('source') === $source)>{{ $source }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Sắp xếp</label>
                <select name="sort" class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500">
                    <option value="">Mới nhất</option>
                    <option value="oldest" @selected(request('sort') === 'oldest')>Cũ nhất</option>
                    <option value="email_asc" @selected(request('sort') === 'email_asc')>Email A → Z</option>
                    <option value="email_desc" @selected(request('sort') === 'email_desc')>Email Z → A</option>
                    <option value="updated_desc" @selected(request('sort') === 'updated_desc')>Mới cập nhật</option>
                </select>
            </div>

            <div class="flex flex-wrap gap-3 md:col-span-2 xl:col-span-6">
                <button type="submit" class="rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white">Lọc dữ liệu</button>
                <a href="{{ route('admin.newsletter-subscribers.index') }}" class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-700">Đặt lại</a>
            </div>
        </form>
    </section>

    <section class="overflow-hidden rounded-xl border border-gray-200 bg-white">
        <div class="border-b border-gray-200 px-5 py-4">
            <h3 class="text-lg font-bold text-gray-900">Danh sách đăng ký Newsletter</h3>
            <p class="mt-1 text-sm text-gray-500">Hiển thị {{ $subscribers->count() }} / {{ $subscribers->total() }} kết quả.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Người đăng ký</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Nguồn</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">Xác minh</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">Trạng thái</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Thời gian</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase text-gray-500">Thao tác</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @forelse ($subscribers as $subscriber)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <div class="min-w-[250px]">
                                    <a href="{{ route('admin.newsletter-subscribers.show', $subscriber->id) }}" class="font-semibold text-gray-900 hover:text-pink-600">
                                        {{ $subscriber->name ?: ($subscriber->user_name ?: 'Không có tên') }}
                                    </a>
                                    <p class="mt-1 text-sm text-gray-600">{{ $subscriber->email }}</p>
                                    <span class="mt-2 inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $subscriber->user_id ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700' }}">
                                        {{ $subscriber->user_id ? 'Thành viên' : 'Khách' }}
                                    </span>
                                </div>
                            </td>

                            <td class="px-5 py-4 text-sm text-gray-600">{{ $subscriber->source ?: 'Không rõ' }}</td>

                            <td class="px-5 py-4 text-center">
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $subscriber->verified_at ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700' }}">
                                    {{ $subscriber->verified_at ? 'Đã xác minh' : 'Chưa xác minh' }}
                                </span>
                            </td>

                            <td class="px-5 py-4 text-center">
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $subscriber->status ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $subscriber->status ? 'Đang hoạt động' : 'Đã hủy' }}
                                </span>
                            </td>

                            <td class="px-5 py-4 text-sm text-gray-600">
                                <p>Đăng ký: {{ $subscriber->subscribed_at ? \Carbon\Carbon::parse($subscriber->subscribed_at)->format('d/m/Y H:i') : 'Chưa có' }}</p>
                                @if ($subscriber->unsubscribed_at)
                                    <p class="mt-1 text-xs text-red-500">Hủy: {{ \Carbon\Carbon::parse($subscriber->unsubscribed_at)->format('d/m/Y H:i') }}</p>
                                @endif
                            </td>

                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('admin.newsletter-subscribers.show', $subscriber->id) }}" class="inline-flex rounded-lg border border-gray-300 px-3 py-2 text-xs font-bold text-gray-700 hover:bg-gray-50">CHI TIẾT</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-16 text-center text-gray-500">Không tìm thấy người đăng ký.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($subscribers->hasPages())
            <div class="border-t border-gray-200 px-5 py-4">{{ $subscribers->links() }}</div>
        @endif
    </section>
</div>
@endsection
