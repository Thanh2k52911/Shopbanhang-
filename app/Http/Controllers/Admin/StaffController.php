<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStaffRequest;
use App\Http\Requests\Admin\UpdateStaffRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Throwable;

class StaffController extends Controller
{
    public function index(Request $request): View
    {
        $staffRoleNames = Role::query()->where('name', '!=', 'customer')->pluck('name');

        $staff = User::query()
            ->with('roles:id,name,display_name')
            ->whereHas('roles', fn ($q) => $q->whereIn('roles.name', $staffRoleNames))
            ->when($request->filled('keyword'), function ($q) use ($request): void {
                $keyword = trim((string) $request->input('keyword'));
                $q->where(fn ($sub) => $sub->where('name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%"));
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('role'), fn ($q) => $q->whereHas('roles', fn ($r) => $r->where('roles.name', $request->input('role'))))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.staff.index', [
            'staff' => $staff,
            'roles' => Role::query()->where('name', '!=', 'customer')->orderBy('display_name')->get(),
            'statistics' => [
                'total' => User::query()->whereHas('roles', fn ($q) => $q->where('roles.name', '!=', 'customer'))->count(),
                'active' => User::query()->where('status', 'active')->whereHas('roles', fn ($q) => $q->where('roles.name', '!=', 'customer'))->count(),
                'blocked' => User::query()->where('status', 'blocked')->whereHas('roles', fn ($q) => $q->where('roles.name', '!=', 'customer'))->count(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.staff.create', ['roles' => $this->assignableRoles()]);
    }

    public function store(StoreStaffRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $staff = DB::transaction(function () use ($validated): User {
                $user = User::query()->create([
                    'name' => trim($validated['name']),
                    'email' => trim($validated['email']),
                    'password' => Hash::make($validated['password']),
                    'status' => $validated['status'],
                    'email_verified_at' => ! empty($validated['email_verified']) ? now() : null,
                ]);
                $user->roles()->sync($validated['role_ids']);
                return $user;
            }, 3);

            return redirect()->route('admin.staff.edit', $staff)->with('success', 'Tạo tài khoản nhân viên thành công.');
        } catch (Throwable $e) {
            report($e);
            return back()->withInput()->with('error', 'Không thể tạo tài khoản nhân viên.');
        }
    }

    public function edit(User $staff): View
    {
        $this->ensureStaff($staff);
        $staff->load('roles:id,name,display_name');
        return view('admin.staff.edit', ['staff' => $staff, 'roles' => $this->assignableRoles()]);
    }

    public function update(UpdateStaffRequest $request, User $staff): RedirectResponse
    {
        $this->ensureStaff($staff);
        abort_if($staff->is(auth()->user()) && $request->input('status') !== 'active', 422, 'Không thể khóa tài khoản đang đăng nhập.');
        $validated = $request->validated();

        DB::transaction(function () use ($staff, $validated): void {
            $data = [
                'name' => trim($validated['name']),
                'email' => trim($validated['email']),
                'status' => $validated['status'],
                'email_verified_at' => ! empty($validated['email_verified']) ? ($staff->email_verified_at ?: now()) : null,
                'blocked_at' => $validated['status'] === 'blocked' ? now() : null,
                'blocked_reason' => $validated['status'] === 'blocked' ? ($validated['blocked_reason'] ?? null) : null,
            ];
            if (! empty($validated['password'])) {
                $data['password'] = Hash::make($validated['password']);
            }
            $staff->update($data);
            $staff->roles()->sync($validated['role_ids']);
        }, 3);

        return back()->with('success', 'Cập nhật nhân viên thành công.');
    }

    public function destroy(User $staff): RedirectResponse
    {
        $this->ensureStaff($staff);
        abort_if($staff->is(auth()->user()), 422, 'Không thể xóa tài khoản đang đăng nhập.');
        $staff->delete();
        return redirect()->route('admin.staff.index')->with('success', 'Đã xóa tài khoản nhân viên.');
    }

    private function assignableRoles()
    {
        return Role::query()->where('name', '!=', 'customer')->orderBy('display_name')->get();
    }

    private function ensureStaff(User $user): void
    {
        abort_unless($user->roles()->where('roles.name', '!=', 'customer')->exists(), 404);
    }
}
