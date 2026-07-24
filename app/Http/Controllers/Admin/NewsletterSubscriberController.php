<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class NewsletterSubscriberController extends Controller
{
    /**
     * Danh sách người đăng ký Newsletter.
     */
    public function index(Request $request): View
    {
        $query = DB::table('newsletter_subscribers as ns')
            ->leftJoin('users as u', 'ns.user_id', '=', 'u.id')
            ->select([
                'ns.id',
                'ns.email',
                'ns.user_id',
                'ns.name',
                'ns.status',
                'ns.source',
                'ns.verified_at',
                'ns.subscribed_at',
                'ns.unsubscribed_at',
                'ns.created_at',
                'ns.updated_at',

                'u.name as user_name',
                'u.email as user_email',
                'u.status as user_status',
            ]);

        $keyword = trim((string) $request->input('keyword'));

        if ($keyword !== '') {
            $query->where(function (Builder $builder) use ($keyword): void {
                $builder
                    ->where('ns.email', 'like', '%' . $keyword . '%')
                    ->orWhere('ns.name', 'like', '%' . $keyword . '%')
                    ->orWhere('ns.source', 'like', '%' . $keyword . '%')
                    ->orWhere('u.name', 'like', '%' . $keyword . '%');
            });
        }

        match ($request->input('status')) {
            'active' => $query->where('ns.status', true),
            'inactive' => $query->where('ns.status', false),
            default => null,
        };

        match ($request->input('verification')) {
            'verified' => $query->whereNotNull('ns.verified_at'),
            'unverified' => $query->whereNull('ns.verified_at'),
            default => null,
        };

        match ($request->input('member_type')) {
            'member' => $query->whereNotNull('ns.user_id'),
            'guest' => $query->whereNull('ns.user_id'),
            default => null,
        };

        if ($request->filled('source')) {
            $query->where('ns.source', $request->input('source'));
        }

        match ($request->input('sort')) {
            'oldest' => $query->orderBy('ns.subscribed_at'),
            'email_asc' => $query->orderBy('ns.email'),
            'email_desc' => $query->orderByDesc('ns.email'),
            'updated_desc' => $query->orderByDesc('ns.updated_at'),
            default => $query->orderByDesc('ns.subscribed_at'),
        };

        $subscribers = $query
            ->paginate(20)
            ->withQueryString();

        $statistics = DB::table('newsletter_subscribers')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END), 0) as active'
            )
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END), 0) as inactive'
            )
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN verified_at IS NOT NULL THEN 1 ELSE 0 END), 0) as verified'
            )
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN verified_at IS NULL THEN 1 ELSE 0 END), 0) as unverified'
            )
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN user_id IS NOT NULL THEN 1 ELSE 0 END), 0) as members'
            )
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN user_id IS NULL THEN 1 ELSE 0 END), 0) as guests'
            )
            ->first();

        $sources = DB::table('newsletter_subscribers')
            ->whereNotNull('source')
            ->where('source', '!=', '')
            ->select('source')
            ->distinct()
            ->orderBy('source')
            ->pluck('source');

        return view('admin.newsletter-subscribers.index', [
            'subscribers' => $subscribers,
            'statistics' => $statistics,
            'sources' => $sources,
        ]);
    }

    /**
     * Chi tiết người đăng ký.
     */
    public function show(int $subscriber): View
    {
        $subscriberDetail = DB::table('newsletter_subscribers as ns')
            ->leftJoin('users as u', 'ns.user_id', '=', 'u.id')
            ->where('ns.id', $subscriber)
            ->select([
                'ns.id',
                'ns.email',
                'ns.user_id',
                'ns.name',
                'ns.status',
                'ns.source',
                'ns.verification_token',
                'ns.unsubscribe_token',
                'ns.verified_at',
                'ns.subscribed_at',
                'ns.unsubscribed_at',
                'ns.created_at',
                'ns.updated_at',

                'u.name as user_name',
                'u.email as user_email',
                'u.status as user_status',
                'u.last_login_at',
                'u.last_login_ip',
            ])
            ->first();

        abort_if(! $subscriberDetail, 404);

        $sameDomainCount = DB::table('newsletter_subscribers')
            ->where('email', 'like', '%@' . $this->emailDomain($subscriberDetail->email))
            ->count();

        return view('admin.newsletter-subscribers.show', [
            'subscriber' => $subscriberDetail,
            'sameDomainCount' => $sameDomainCount,
        ]);
    }

    /**
     * Tạo người đăng ký thủ công.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('newsletter_subscribers', 'email'),
            ],
            'name' => [
                'nullable',
                'string',
                'max:150',
            ],
            'source' => [
                'nullable',
                'string',
                'max:50',
            ],
            'verified' => [
                'nullable',
                'boolean',
            ],
        ], [
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không hợp lệ.',
            'email.unique' => 'Email này đã đăng ký Newsletter.',
        ]);

        try {
            DB::table('newsletter_subscribers')->insert([
                'email' => strtolower(trim($validated['email'])),
                'user_id' => DB::table('users')
                    ->whereRaw('LOWER(email) = ?', [
                        strtolower(trim($validated['email'])),
                    ])
                    ->value('id'),
                'name' => $this->nullableTrim($validated['name'] ?? null),
                'status' => true,
                'source' => $this->nullableTrim($validated['source'] ?? 'admin'),
                'verification_token' => ! empty($validated['verified'])
                    ? null
                    : Str::random(64),
                'unsubscribe_token' => Str::random(64),
                'verified_at' => ! empty($validated['verified'])
                    ? now()
                    : null,
                'subscribed_at' => now(),
                'unsubscribed_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return back()->with(
                'success',
                'Thêm người đăng ký Newsletter thành công.'
            );
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with(
                    'error',
                    app()->isLocal()
                        ? 'Lỗi thêm Newsletter: ' . $exception->getMessage()
                        : 'Không thể thêm người đăng ký.'
                );
        }
    }

    /**
     * Bật hoặc tắt trạng thái đăng ký.
     */
    public function updateStatus(
        Request $request,
        int $subscriber
    ): RedirectResponse {
        $subscriberDetail = DB::table('newsletter_subscribers')
            ->where('id', $subscriber)
            ->first();

        abort_if(! $subscriberDetail, 404);

        $validated = $request->validate([
            'status' => [
                'required',
                'boolean',
            ],
        ]);

        $status = (bool) $validated['status'];

        try {
            DB::table('newsletter_subscribers')
                ->where('id', $subscriberDetail->id)
                ->update([
                    'status' => $status,
                    'unsubscribed_at' => $status ? null : now(),
                    'subscribed_at' => $status
                        ? ($subscriberDetail->subscribed_at ?: now())
                        : $subscriberDetail->subscribed_at,
                    'unsubscribe_token' => $subscriberDetail->unsubscribe_token
                        ?: Str::random(64),
                    'updated_at' => now(),
                ]);

            return back()->with(
                'success',
                $status
                    ? 'Đã kích hoạt lại đăng ký Newsletter.'
                    : 'Đã hủy kích hoạt đăng ký Newsletter.'
            );
        } catch (Throwable $exception) {
            report($exception);

            return back()->with(
                'error',
                app()->isLocal()
                    ? 'Lỗi cập nhật trạng thái: ' . $exception->getMessage()
                    : 'Không thể cập nhật trạng thái.'
            );
        }
    }

    /**
     * Đánh dấu email đã xác minh.
     */
    public function verify(int $subscriber): RedirectResponse
    {
        $subscriberDetail = DB::table('newsletter_subscribers')
            ->where('id', $subscriber)
            ->first();

        abort_if(! $subscriberDetail, 404);

        if ($subscriberDetail->verified_at) {
            return back()->with(
                'error',
                'Email này đã được xác minh.'
            );
        }

        try {
            DB::table('newsletter_subscribers')
                ->where('id', $subscriberDetail->id)
                ->update([
                    'verified_at' => now(),
                    'verification_token' => null,
                    'status' => true,
                    'unsubscribed_at' => null,
                    'updated_at' => now(),
                ]);

            return back()->with(
                'success',
                'Đã xác minh email Newsletter.'
            );
        } catch (Throwable $exception) {
            report($exception);

            return back()->with(
                'error',
                app()->isLocal()
                    ? 'Lỗi xác minh email: ' . $exception->getMessage()
                    : 'Không thể xác minh email.'
            );
        }
    }

    /**
     * Xóa người đăng ký.
     */
    public function destroy(int $subscriber): RedirectResponse
    {
        $subscriberDetail = DB::table('newsletter_subscribers')
            ->where('id', $subscriber)
            ->first();

        abort_if(! $subscriberDetail, 404);

        try {
            DB::table('newsletter_subscribers')
                ->where('id', $subscriberDetail->id)
                ->delete();

            return redirect()
                ->route('admin.newsletter-subscribers.index')
                ->with(
                    'success',
                    'Đã xóa người đăng ký Newsletter.'
                );
        } catch (Throwable $exception) {
            report($exception);

            return back()->with(
                'error',
                app()->isLocal()
                    ? 'Lỗi xóa Newsletter: ' . $exception->getMessage()
                    : 'Không thể xóa người đăng ký.'
            );
        }
    }

    private function nullableTrim(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value !== ''
            ? $value
            : null;
    }

    private function emailDomain(string $email): string
    {
        return strtolower((string) str($email)->after('@'));
    }
}
