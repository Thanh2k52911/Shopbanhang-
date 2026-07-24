@extends('admin.layouts.master')

@section('title', 'Chi tiết Newsletter')
@section('page-title', 'Chi tiết Newsletter')
@section('page-description', 'Xem trạng thái đăng ký, xác minh và thông tin người nhận tin.')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">{{ $subscriber->email }}</h2>
            <p class="mt-1 text-sm text-gray-500">Subscriber #{{ $subscriber->id }}</p>
        </div>

        <a href="{{ route('admin.newsletter-subscribers.index') }}" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700">Quay lại</a>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ session('error') }}</div>
    @endif

    <div class="admin-responsive-sidebar-layout grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,2fr)_360px]">
        <div class="space-y-6">
            <section class="rounded-xl border border-gray-200 bg-white p-6">
                <h3 class="text-lg font-bold text-gray-900">Thông tin đăng ký</h3>

                <dl class="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div>
                        <dt class="text-sm text-gray-500">Tên</dt>
                        <dd class="mt-1 font-semibold text-gray-900">{{ $subscriber->name ?: ($subscriber->user_name ?: 'Chưa có') }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm text-gray-500">Email</dt>
                        <dd class="mt-1 font-semibold text-gray-900">{{ $subscriber->email }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm text-gray-500">Nguồn đăng ký</dt>
                        <dd class="mt-1 font-semibold text-gray-900">{{ $subscriber->source ?: 'Không rõ' }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm text-gray-500">Loại</dt>
                        <dd class="mt-1 font-semibold text-gray-900">{{ $subscriber->user_id ? 'Thành viên' : 'Khách' }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm text-gray-500">Trạng thái</dt>
                        <dd class="mt-1">
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $subscriber->status ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $subscriber->status ? 'Đang hoạt động' : 'Đã hủy đăng ký' }}
                            </span>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-gray-500">Xác minh</dt>
                        <dd class="mt-1">
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $subscriber->verified_at ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700' }}">
                                {{ $subscriber->verified_at ? 'Đã xác minh' : 'Chưa xác minh' }}
                            </span>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-gray-500">Đăng ký lúc</dt>
                        <dd class="mt-1 font-semibold text-gray-900">{{ $subscriber->subscribed_at ? \Carbon\Carbon::parse($subscriber->subscribed_at)->format('d/m/Y H:i') : 'Chưa có' }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm text-gray-500">Hủy lúc</dt>
                        <dd class="mt-1 font-semibold text-gray-900">{{ $subscriber->unsubscribed_at ? \Carbon\Carbon::parse($subscriber->unsubscribed_at)->format('d/m/Y H:i') : 'Chưa có' }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm text-gray-500">Xác minh lúc</dt>
                        <dd class="mt-1 font-semibold text-gray-900">{{ $subscriber->verified_at ? \Carbon\Carbon::parse($subscriber->verified_at)->format('d/m/Y H:i') : 'Chưa có' }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm text-gray-500">Cập nhật gần nhất</dt>
                        <dd class="mt-1 font-semibold text-gray-900">{{ \Carbon\Carbon::parse($subscriber->updated_at)->format('d/m/Y H:i') }}</dd>
                    </div>
                </dl>
            </section>

            @if ($subscriber->user_id)
                <section class="rounded-xl border border-gray-200 bg-white p-6">
                    <h3 class="text-lg font-bold text-gray-900">Tài khoản liên kết</h3>

                    <dl class="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div>
                            <dt class="text-sm text-gray-500">Tên tài khoản</dt>
                            <dd class="mt-1 font-semibold text-gray-900">{{ $subscriber->user_name }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm text-gray-500">Email tài khoản</dt>
                            <dd class="mt-1 font-semibold text-gray-900">{{ $subscriber->user_email }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm text-gray-500">Trạng thái tài khoản</dt>
                            <dd class="mt-1 font-semibold text-gray-900">{{ $subscriber->user_status }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm text-gray-500">Đăng nhập gần nhất</dt>
                            <dd class="mt-1 font-semibold text-gray-900">{{ $subscriber->last_login_at ? \Carbon\Carbon::parse($subscriber->last_login_at)->format('d/m/Y H:i') : 'Chưa có' }}</dd>
                        </div>
                    </dl>
                </section>
            @endif

            <section class="rounded-xl border border-gray-200 bg-white p-6">
                <h3 class="text-lg font-bold text-gray-900">Thông tin kỹ thuật</h3>

                <dl class="mt-5 space-y-4 text-sm">
                    <div>
                        <dt class="text-gray-500">Verification token</dt>
                        <dd class="mt-1 break-all font-mono text-xs text-gray-700">{{ $subscriber->verification_token ?: 'Không có' }}</dd>
                    </div>

                    <div>
                        <dt class="text-gray-500">Unsubscribe token</dt>
                        <dd class="mt-1 break-all font-mono text-xs text-gray-700">{{ $subscriber->unsubscribe_token ?: 'Không có' }}</dd>
                    </div>

                    <div>
                        <dt class="text-gray-500">Số đăng ký cùng tên miền email</dt>
                        <dd class="mt-1 font-semibold text-gray-900">{{ number_format((int) $sameDomainCount) }}</dd>
                    </div>
                </dl>
            </section>
        </div>

        <aside class="space-y-6">
            <section class="rounded-xl border border-gray-200 bg-white p-5">
                <h3 class="text-lg font-bold text-gray-900">Thay đổi trạng thái</h3>

                <form method="POST" action="{{ route('admin.newsletter-subscribers.update-status', $subscriber->id) }}" class="mt-4 space-y-4">
                    @csrf
                    @method('PATCH')

                    <select name="status" class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500">
                        <option value="1" @selected((bool) $subscriber->status === true)>Đang hoạt động</option>
                        <option value="0" @selected((bool) $subscriber->status === false)>Hủy đăng ký</option>
                    </select>

                    <button type="submit" class="w-full rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white">Cập nhật trạng thái</button>
                </form>
            </section>

            @if (! $subscriber->verified_at)
                <section class="rounded-xl border border-blue-200 bg-blue-50 p-5">
                    <h3 class="text-lg font-bold text-blue-900">Xác minh email</h3>
                    <p class="mt-2 text-sm leading-6 text-blue-700">Đánh dấu email này là đã xác minh thủ công.</p>

                    <form method="POST" action="{{ route('admin.newsletter-subscribers.verify', $subscriber->id) }}" class="mt-4">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="w-full rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white">Xác minh email</button>
                    </form>
                </section>
            @endif

            @if ($subscriber->user_id)
                <section class="rounded-xl border border-gray-200 bg-white p-5">
                    <h3 class="text-lg font-bold text-gray-900">Tài khoản khách hàng</h3>
                    <a href="{{ route('admin.customers.show', $subscriber->user_id) }}" class="mt-4 inline-flex w-full justify-center rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700">Xem khách hàng</a>
                </section>
            @endif

            <section class="rounded-xl border border-red-200 bg-red-50 p-5">
                <h3 class="text-lg font-bold text-red-900">Xóa đăng ký</h3>
                <p class="mt-2 text-sm leading-6 text-red-700">Thao tác này sẽ xóa vĩnh viễn bản ghi Newsletter.</p>

                <form method="POST" action="{{ route('admin.newsletter-subscribers.destroy', $subscriber->id) }}" class="mt-4" onsubmit="return confirm('Xóa người đăng ký Newsletter này?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white">Xóa đăng ký</button>
                </form>
            </section>
        </aside>
    </div>
</div>
@endsection
