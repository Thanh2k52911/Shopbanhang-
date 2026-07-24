<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreShippingMethodRequest;
use App\Http\Requests\Admin\UpdateShippingMethodRequest;
use App\Models\ShippingMethod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

class ShippingMethodController extends Controller
{
    public function index(Request $request): View
    {
        $query = ShippingMethod::query()->withCount('shipments');

        if ($request->filled('keyword')) {
            $keyword = trim((string) $request->input('keyword'));

            $query->where(function ($builder) use ($keyword): void {
                $builder
                    ->where('name', 'like', "%{$keyword}%")
                    ->orWhere('code', 'like', "%{$keyword}%")
                    ->orWhere('provider', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', (bool) $request->integer('status'));
        }

        match ($request->input('sort')) {
            'name_asc' => $query->orderBy('name'),
            'name_desc' => $query->orderByDesc('name'),
            'fee_asc' => $query->orderBy('base_fee'),
            'fee_desc' => $query->orderByDesc('base_fee'),
            'oldest' => $query->orderBy('id'),
            default => $query->orderBy('sort_order')->orderByDesc('id'),
        };

        $shippingMethods = $query
            ->paginate(20)
            ->withQueryString();

        $statistics = [
            'total' => ShippingMethod::count(),
            'active' => ShippingMethod::where('status', true)->count(),
            'inactive' => ShippingMethod::where('status', false)->count(),
            'free_shipping' => ShippingMethod::whereNotNull('free_shipping_minimum')->count(),
        ];

        return view('admin.shipping-methods.index', compact('shippingMethods', 'statistics'));
    }

    public function create(): View
    {
        return view('admin.shipping-methods.create');
    }

    public function store(StoreShippingMethodRequest $request): RedirectResponse
    {
        try {
            $shippingMethod = DB::transaction(function () use ($request): ShippingMethod {
                return ShippingMethod::create($request->validated());
            }, 3);

            return redirect()
                ->route('admin.shipping-methods.show', $shippingMethod)
                ->with('success', 'Thêm phương thức vận chuyển thành công.');
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with('error', 'Không thể thêm phương thức vận chuyển.');
        }
    }

    public function show(ShippingMethod $shippingMethod): View
    {
        $shippingMethod->loadCount('shipments');

        $recentShipments = $shippingMethod->shipments()
            ->with('order:id,order_code,customer_name,total_amount')
            ->latest('id')
            ->limit(15)
            ->get();

        return view('admin.shipping-methods.show', compact('shippingMethod', 'recentShipments'));
    }

    public function edit(ShippingMethod $shippingMethod): View
    {
        return view('admin.shipping-methods.edit', compact('shippingMethod'));
    }

    public function update(
        UpdateShippingMethodRequest $request,
        ShippingMethod $shippingMethod
    ): RedirectResponse {
        try {
            DB::transaction(function () use ($request, $shippingMethod): void {
                $shippingMethod->update($request->validated());
            }, 3);

            return redirect()
                ->route('admin.shipping-methods.show', $shippingMethod)
                ->with('success', 'Cập nhật phương thức vận chuyển thành công.');
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with('error', 'Không thể cập nhật phương thức vận chuyển.');
        }
    }

    public function toggle(ShippingMethod $shippingMethod): RedirectResponse
    {
        $shippingMethod->update([
            'status' => ! $shippingMethod->status,
        ]);

        return back()->with(
            'success',
            $shippingMethod->status
                ? 'Đã bật phương thức vận chuyển.'
                : 'Đã tắt phương thức vận chuyển.'
        );
    }

    public function destroy(ShippingMethod $shippingMethod): RedirectResponse
    {
        if ($shippingMethod->shipments()->exists()) {
            return back()->with(
                'error',
                'Không thể xóa phương thức đã phát sinh vận đơn. Hãy tắt trạng thái sử dụng.'
            );
        }

        $shippingMethod->delete();

        return redirect()
            ->route('admin.shipping-methods.index')
            ->with('success', 'Xóa phương thức vận chuyển thành công.');
    }
}
