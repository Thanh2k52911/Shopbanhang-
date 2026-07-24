<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreUserAddressRequest;
use App\Http\Requests\Client\UpdateUserAddressRequest;
use App\Models\UserAddress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UserAddressController extends Controller
{
    public function index(): View
    {
        $addresses = UserAddress::query()
            ->where('user_id', auth()->id())
            ->orderByDesc('is_default')
            ->latest('id')
            ->get();

        return view('client.account.addresses.index', compact('addresses'));
    }

    public function create(): View
    {
        return view('client.account.addresses.create');
    }

    public function store(StoreUserAddressRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request): void {
            $userId = (int) $request->user()->id;
            $validated = $request->validated();

            $hasAddress = UserAddress::query()
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->exists();

            $makeDefault = !$hasAddress
                || (bool) ($validated['is_default'] ?? false);

            if ($makeDefault) {
                UserAddress::query()
                    ->where('user_id', $userId)
                    ->update(['is_default' => false]);
            }

            UserAddress::query()->create([
                'user_id' => $userId,
                'receiver_name' => $validated['receiver_name'],
                'phone' => $validated['phone'],
                'province' => $validated['province'],
                'district' => $validated['district'],
                'ward' => $validated['ward'],
                'address' => $validated['address'],
                'is_default' => $makeDefault,
            ]);
        }, 3);

        return redirect()
            ->route('account.addresses.index')
            ->with('address_success', 'Đã thêm địa chỉ nhận hàng.');
    }

    public function edit(UserAddress $address): View
    {
        $this->ensureOwner($address);

        return view('client.account.addresses.edit', compact('address'));
    }

    public function update(
        UpdateUserAddressRequest $request,
        UserAddress $address
    ): RedirectResponse {
        $this->ensureOwner($address);

        DB::transaction(function () use ($request, $address): void {
            $userId = (int) $request->user()->id;
            $validated = $request->validated();
            $makeDefault = (bool) ($validated['is_default'] ?? false);

            $lockedAddress = UserAddress::query()
                ->whereKey($address->id)
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($makeDefault) {
                UserAddress::query()
                    ->where('user_id', $userId)
                    ->whereKeyNot($lockedAddress->id)
                    ->update(['is_default' => false]);
            }

            $lockedAddress->update([
                'receiver_name' => $validated['receiver_name'],
                'phone' => $validated['phone'],
                'province' => $validated['province'],
                'district' => $validated['district'],
                'ward' => $validated['ward'],
                'address' => $validated['address'],
                'is_default' => $makeDefault || $lockedAddress->is_default,
            ]);
        }, 3);

        return redirect()
            ->route('account.addresses.index')
            ->with('address_success', 'Đã cập nhật địa chỉ nhận hàng.');
    }

    public function setDefault(UserAddress $address): RedirectResponse
    {
        $this->ensureOwner($address);

        DB::transaction(function () use ($address): void {
            UserAddress::query()
                ->where('user_id', auth()->id())
                ->update(['is_default' => false]);

            UserAddress::query()
                ->whereKey($address->id)
                ->where('user_id', auth()->id())
                ->update(['is_default' => true]);
        }, 3);

        return back()->with('address_success', 'Đã đặt làm địa chỉ mặc định.');
    }

    public function destroy(UserAddress $address): RedirectResponse
    {
        $this->ensureOwner($address);

        DB::transaction(function () use ($address): void {
            $userId = (int) auth()->id();
            $wasDefault = (bool) $address->is_default;

            $address->delete();

            if ($wasDefault) {
                $nextAddress = UserAddress::query()
                    ->where('user_id', $userId)
                    ->latest('id')
                    ->lockForUpdate()
                    ->first();

                $nextAddress?->update(['is_default' => true]);
            }
        }, 3);

        return back()->with('address_success', 'Đã xóa địa chỉ nhận hàng.');
    }

    private function ensureOwner(UserAddress $address): void
    {
        abort_unless((int) $address->user_id === (int) auth()->id(), 404);
    }
}
