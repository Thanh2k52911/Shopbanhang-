<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RoleController extends Controller
{
    private const PROTECTED_ROLES = ['super_admin', 'admin', 'customer'];

    public function index(Request $request): View
    {
        $roles = Role::query()->withCount(['users', 'permissions'])
            ->when($request->filled('keyword'), function ($q) use ($request): void {
                $keyword = trim((string) $request->input('keyword'));
                $q->where(fn ($sub) => $sub->where('name', 'like', "%{$keyword}%")
                    ->orWhere('display_name', 'like', "%{$keyword}%"));
            })->orderBy('id')->paginate(20)->withQueryString();

        return view('admin.roles.index', compact('roles'));
    }

    public function create(): View
    {
        return view('admin.roles.create', ['permissionGroups' => $this->permissionGroups()]);
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $role = DB::transaction(function () use ($validated): Role {
            $role = Role::query()->create([
                'name' => $validated['name'],
                'display_name' => $validated['display_name'],
                'description' => $validated['description'] ?? null,
            ]);
            $role->permissions()->sync($validated['permission_ids'] ?? []);
            return $role;
        }, 3);

        return redirect()->route('admin.roles.edit', $role)->with('success', 'Tạo vai trò thành công.');
    }

    public function edit(Role $role): View
    {
        $role->load('permissions:id,name');
        return view('admin.roles.edit', ['role' => $role, 'permissionGroups' => $this->permissionGroups()]);
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $validated = $request->validated();
        DB::transaction(function () use ($role, $validated): void {
            $data = ['display_name' => $validated['display_name'], 'description' => $validated['description'] ?? null];
            if (! in_array($role->name, self::PROTECTED_ROLES, true)) {
                $data['name'] = $validated['name'];
            }
            $role->update($data);
            if ($role->name !== 'super_admin') {
                $role->permissions()->sync($validated['permission_ids'] ?? []);
            }
        }, 3);
        return back()->with('success', 'Cập nhật vai trò thành công.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        abort_if(in_array($role->name, self::PROTECTED_ROLES, true), 422, 'Không thể xóa vai trò hệ thống.');
        abort_if($role->users()->exists(), 422, 'Vai trò đang được gán cho người dùng.');
        $role->delete();
        return redirect()->route('admin.roles.index')->with('success', 'Đã xóa vai trò.');
    }

    private function permissionGroups()
    {
        return Permission::query()->orderBy('name')->get()->groupBy(fn (Permission $p) => str($p->name)->before('.')->value());
    }
}
