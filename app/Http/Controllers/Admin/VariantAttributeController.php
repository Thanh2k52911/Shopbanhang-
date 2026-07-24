<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreVariantAttributeRequest;
use App\Http\Requests\Admin\UpdateVariantAttributeRequest;
use App\Models\VariantAttribute;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class VariantAttributeController extends Controller
{
    public function index(Request $request): View
    {
        $keyword = trim((string) $request->input('keyword'));

        $attributes = VariantAttribute::query()
            ->withCount([
                'values',
                'values as used_values_count' => fn ($query) => $query->whereHas('variants'),
            ])
            ->when($keyword !== '', fn ($query) => $query->where('name', 'like', "%{$keyword}%"))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $statistics = [
            'attributes' => VariantAttribute::query()->count(),
            'values' => DB::table('variant_values')->count(),
            'used_values' => DB::table('variant_values as vv')
                ->whereExists(function ($query): void {
                    $query->selectRaw('1')
                        ->from('product_variant_values as pvv')
                        ->whereColumn('pvv.value_id', 'vv.id');
                })
                ->count(),
        ];

        return view('admin.variant-attributes.index', compact('attributes', 'statistics'));
    }

    public function create(): View
    {
        return view('admin.variant-attributes.create');
    }

    public function store(StoreVariantAttributeRequest $request): RedirectResponse
    {
        $attribute = VariantAttribute::query()->create($request->validated());

        return redirect()
            ->route('admin.variant-attributes.show', $attribute)
            ->with('success', 'Đã thêm thuộc tính biến thể.');
    }

    public function show(VariantAttribute $variantAttribute): View
    {
        $variantAttribute->load([
            'values' => fn ($query) => $query
                ->withCount('variants')
                ->orderBy('value'),
        ]);

        return view('admin.variant-attributes.show', compact('variantAttribute'));
    }

    public function edit(VariantAttribute $variantAttribute): View
    {
        return view('admin.variant-attributes.edit', compact('variantAttribute'));
    }

    public function update(
        UpdateVariantAttributeRequest $request,
        VariantAttribute $variantAttribute
    ): RedirectResponse {
        $variantAttribute->update($request->validated());

        return redirect()
            ->route('admin.variant-attributes.show', $variantAttribute)
            ->with('success', 'Đã cập nhật thuộc tính biến thể.');
    }

    public function destroy(VariantAttribute $variantAttribute): RedirectResponse
    {
        $isUsed = DB::table('product_variant_values as pvv')
            ->join('variant_values as vv', 'vv.id', '=', 'pvv.value_id')
            ->where('vv.attribute_id', $variantAttribute->id)
            ->exists();

        if ($isUsed) {
            return back()->with(
                'error',
                'Không thể xóa thuộc tính vì có giá trị đang được sử dụng trong biến thể sản phẩm.'
            );
        }

        $variantAttribute->delete();

        return redirect()
            ->route('admin.variant-attributes.index')
            ->with('success', 'Đã xóa thuộc tính và các giá trị chưa sử dụng.');
    }
}
