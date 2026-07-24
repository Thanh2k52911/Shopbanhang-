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

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $query = DB::table('users as u')
            ->whereNull('u.deleted_at')
            ->whereExists(function (Builder $builder): void {
                $builder->selectRaw('1')
                    ->from('user_roles as ur')
                    ->join('roles as r', 'ur.role_id', '=', 'r.id')
                    ->whereColumn('ur.user_id', 'u.id')
                    ->where('r.name', 'customer');
            })
            ->select([
                'u.id', 'u.name', 'u.email', 'u.email_verified_at',
                'u.avatar', 'u.status', 'u.blocked_at', 'u.blocked_reason',
                'u.last_login_at', 'u.last_login_ip', 'u.created_at', 'u.updated_at',
            ])
            ->selectSub(function (Builder $builder): void {
                $builder->from('orders')->selectRaw('COUNT(*)')
                    ->whereColumn('orders.user_id', 'u.id')
                    ->whereNull('orders.deleted_at');
            }, 'orders_count')
            ->selectSub(function (Builder $builder): void {
                $builder->from('orders')->selectRaw('COALESCE(SUM(total_amount), 0)')
                    ->whereColumn('orders.user_id', 'u.id')
                    ->whereNull('orders.deleted_at')
                    ->where('orders.order_status', 'completed');
            }, 'completed_spending')
            ->selectSub(function (Builder $builder): void {
                $builder->from('orders')->select('created_at')
                    ->whereColumn('orders.user_id', 'u.id')
                    ->whereNull('orders.deleted_at')
                    ->latest('created_at')
                    ->limit(1);
            }, 'last_order_at')
            ->selectSub(function (Builder $builder): void {
                $builder->from('user_addresses')->selectRaw('COUNT(*)')
                    ->whereColumn('user_addresses.user_id', 'u.id');
            }, 'addresses_count')
            ->selectSub(function (Builder $builder): void {
                $builder->from('loyalty_accounts')->select('available_points')
                    ->whereColumn('loyalty_accounts.user_id', 'u.id')
                    ->limit(1);
            }, 'available_points');

        $keyword = trim((string) $request->input('keyword'));

        if ($keyword !== '') {
            $query->where(function (Builder $builder) use ($keyword): void {
                $builder->where('u.name', 'like', "%{$keyword}%")
                    ->orWhere('u.email', 'like', "%{$keyword}%")
                    ->orWhere('u.last_login_ip', 'like', "%{$keyword}%")
                    ->orWhereExists(function (Builder $addressQuery) use ($keyword): void {
                        $addressQuery->selectRaw('1')
                            ->from('user_addresses as ua')
                            ->whereColumn('ua.user_id', 'u.id')
                            ->where(function (Builder $inner) use ($keyword): void {
                                $inner->where('ua.receiver_name', 'like', "%{$keyword}%")
                                    ->orWhere('ua.phone', 'like', "%{$keyword}%");
                            });
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('u.status', $request->input('status'));
        }

        match ($request->input('verification')) {
            'verified' => $query->whereNotNull('u.email_verified_at'),
            'unverified' => $query->whereNull('u.email_verified_at'),
            default => null,
        };

        match ($request->input('order_status')) {
            'has_orders' => $query->whereExists(function (Builder $builder): void {
                $builder->selectRaw('1')->from('orders')
                    ->whereColumn('orders.user_id', 'u.id')
                    ->whereNull('orders.deleted_at');
            }),
            'no_orders' => $query->whereNotExists(function (Builder $builder): void {
                $builder->selectRaw('1')->from('orders')
                    ->whereColumn('orders.user_id', 'u.id')
                    ->whereNull('orders.deleted_at');
            }),
            default => null,
        };

        match ($request->input('loyalty')) {
            'member' => $query->whereExists(function (Builder $builder): void {
                $builder->selectRaw('1')->from('loyalty_accounts')
                    ->whereColumn('loyalty_accounts.user_id', 'u.id');
            }),
            'non_member' => $query->whereNotExists(function (Builder $builder): void {
                $builder->selectRaw('1')->from('loyalty_accounts')
                    ->whereColumn('loyalty_accounts.user_id', 'u.id');
            }),
            default => null,
        };

        match ($request->input('sort')) {
            'oldest' => $query->orderBy('u.id'),
            'name_asc' => $query->orderBy('u.name'),
            'name_desc' => $query->orderByDesc('u.name'),
            'orders_desc' => $query->orderByDesc('orders_count'),
            'spending_desc' => $query->orderByDesc('completed_spending'),
            'last_order_desc' => $query->orderByDesc('last_order_at'),
            'last_login_desc' => $query->orderByDesc('u.last_login_at'),
            default => $query->orderByDesc('u.id'),
        };

        $customers = $query->paginate(20)->withQueryString();

        $baseQuery = DB::table('users as u')
            ->whereNull('u.deleted_at')
            ->whereExists(function (Builder $builder): void {
                $builder->selectRaw('1')->from('user_roles as ur')
                    ->join('roles as r', 'ur.role_id', '=', 'r.id')
                    ->whereColumn('ur.user_id', 'u.id')
                    ->where('r.name', 'customer');
            });

        $statistics = [
            'total' => (clone $baseQuery)->count(),
            'active' => (clone $baseQuery)->where('u.status', 'active')->count(),
            'inactive' => (clone $baseQuery)->where('u.status', 'inactive')->count(),
            'blocked' => (clone $baseQuery)->where('u.status', 'blocked')->count(),
            'verified' => (clone $baseQuery)->whereNotNull('u.email_verified_at')->count(),
            'loyalty_members' => (clone $baseQuery)->whereExists(function (Builder $builder): void {
                $builder->selectRaw('1')->from('loyalty_accounts')
                    ->whereColumn('loyalty_accounts.user_id', 'u.id');
            })->count(),
        ];

        return view('admin.customers.index', compact('customers', 'statistics'));
    }

    public function show(int $customer): View
    {
        $customerDetail = $this->findCustomer($customer);
        abort_if(! $customerDetail, 404);

        $addresses = DB::table('user_addresses')
            ->where('user_id', $customerDetail->id)
            ->orderByDesc('is_default')
            ->orderByDesc('id')
            ->get();

        $loyaltyAccount = DB::table('loyalty_accounts as la')
            ->leftJoin('loyalty_tiers as lt', 'la.tier_id', '=', 'lt.id')
            ->where('la.user_id', $customerDetail->id)
            ->select(['la.*', 'lt.name as tier_name'])
            ->first();

        $orders = DB::table('orders')
            ->where('user_id', $customerDetail->id)
            ->whereNull('deleted_at')
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $orderStatistics = DB::table('orders')
            ->where('user_id', $customerDetail->id)
            ->whereNull('deleted_at')
            ->selectRaw("
                COUNT(*) as total_orders,
                COALESCE(SUM(CASE WHEN order_status = 'completed' THEN 1 ELSE 0 END), 0) as completed_orders,
                COALESCE(SUM(CASE WHEN order_status = 'cancelled' THEN 1 ELSE 0 END), 0) as cancelled_orders,
                COALESCE(SUM(CASE WHEN order_status = 'completed' THEN total_amount ELSE 0 END), 0) as completed_spending
            ")->first();

        $activityStatistics = [
            'reviews' => DB::table('product_reviews')
                ->where('user_id', $customerDetail->id)
                ->count(),
            'favorites' => DB::table('product_favorites')
                ->where('user_id', $customerDetail->id)
                ->count(),
            'questions' => DB::table('product_questions')
                ->where('user_id', $customerDetail->id)
                ->count(),
            'saved_coupons' => DB::table('saved_coupons')
                ->where('user_id', $customerDetail->id)
                ->count(),
        ];

        $statusHistories = DB::table('user_status_histories as ush')
            ->leftJoin('users as creator', 'ush.created_by', '=', 'creator.id')
            ->where('ush.user_id', $customerDetail->id)
            ->orderByDesc('ush.created_at')
            ->get([
                'ush.id', 'ush.old_status', 'ush.new_status', 'ush.reason',
                'ush.created_at', 'creator.name as creator_name',
                'creator.email as creator_email',
            ]);

        return view('admin.customers.show', [
            'customer' => $customerDetail,
            'addresses' => $addresses,
            'loyaltyAccount' => $loyaltyAccount,
            'orders' => $orders,
            'orderStatistics' => $orderStatistics,
            'statusHistories' => $statusHistories,
            'activityStatistics' => $activityStatistics,
        ]);
    }

    public function edit(int $customer): View
    {
        $customerDetail = $this->findCustomer($customer);
        abort_if(! $customerDetail, 404);

        return view('admin.customers.edit', ['customer' => $customerDetail]);
    }

    public function update(Request $request, int $customer): RedirectResponse
    {
        $customerDetail = $this->findCustomer($customer);
        abort_if(! $customerDetail, 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($customerDetail->id),
            ],
            'email_verified' => ['nullable', 'boolean'],
        ]);

        try {
            DB::table('users')->where('id', $customerDetail->id)->update([
                'name' => trim($validated['name']),
                'email' => trim($validated['email']),
                'email_verified_at' => ((int) ($validated['email_verified'] ?? 0)) === 1
                    ? ($customerDetail->email_verified_at ?: now())
                    : null,
                'updated_at' => now(),
            ]);

            return redirect()->route('admin.customers.show', $customerDetail->id)
                ->with('success', 'Cập nhật khách hàng thành công.');
        } catch (Throwable $exception) {
            report($exception);

            return back()->withInput()->with(
                'error',
                app()->isLocal() ? 'Lỗi: ' . $exception->getMessage() : 'Không thể cập nhật khách hàng.'
            );
        }
    }

    public function updateStatus(Request $request, int $customer): RedirectResponse
    {
        $customerDetail = $this->findCustomer($customer);
        abort_if(! $customerDetail, 404);

        $validated = $request->validate([
            'status' => ['required', Rule::in(['active', 'inactive', 'blocked'])],
            'reason' => [
                Rule::requiredIf(fn (): bool => $request->input('status') !== 'active'),
                'nullable', 'string', 'max:2000',
            ],
        ], [
            'status.required' => 'Vui lòng chọn trạng thái.',
            'status.in' => 'Trạng thái không hợp lệ.',
            'reason.required' => 'Vui lòng nhập lý do thay đổi trạng thái.',
        ]);

        $newStatus = $validated['status'];
        $oldStatus = $customerDetail->status;

        if ($newStatus === $oldStatus) {
            return back()->with('error', 'Tài khoản đang ở trạng thái này.');
        }

        DB::beginTransaction();

        try {
            DB::table('users')->where('id', $customerDetail->id)->update([
                'status' => $newStatus,
                'blocked_at' => $newStatus === 'blocked' ? now() : null,
                'blocked_reason' => $newStatus === 'active'
                    ? null
                    : trim((string) ($validated['reason'] ?? '')),
                'updated_at' => now(),
            ]);

            DB::table('user_status_histories')->insert([
                'user_id' => $customerDetail->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'reason' => $this->nullableTrim($validated['reason'] ?? null),
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return back()->with('success', 'Cập nhật trạng thái tài khoản thành công.');
        } catch (Throwable $exception) {
            DB::rollBack();
            report($exception);

            return back()->withInput()->with(
                'error',
                app()->isLocal() ? 'Lỗi: ' . $exception->getMessage() : 'Không thể cập nhật trạng thái tài khoản.'
            );
        }
    }

    private function findCustomer(int $customer): ?object
    {
        return DB::table('users as u')
            ->where('u.id', $customer)
            ->whereNull('u.deleted_at')
            ->whereExists(function (Builder $builder): void {
                $builder->selectRaw('1')->from('user_roles as ur')
                    ->join('roles as r', 'ur.role_id', '=', 'r.id')
                    ->whereColumn('ur.user_id', 'u.id')
                    ->where('r.name', 'customer');
            })
            ->select([
                'u.id', 'u.name', 'u.email', 'u.email_verified_at', 'u.avatar',
                'u.status', 'u.blocked_at', 'u.blocked_reason',
                'u.last_login_at', 'u.last_login_ip', 'u.created_at', 'u.updated_at',
            ])->first();
    }

    private function nullableTrim(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }
}
