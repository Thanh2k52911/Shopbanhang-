<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreVariantValueRequest;
use App\Http\Requests\Admin\UpdateVariantValueRequest;
use App\Models\VariantAttribute;
use App\Models\VariantValue;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VariantValueController extends Controller
{
    public function create(VariantAttribute $variantAttribute): View
    {
        return view('admin.variant-values.create', compact('variantAttribute'));
    }

    public function store(
        StoreVariantValueRequest $request,
        VariantAttribute $variantAttribute
    ): RedirectResponse {
        $variantAttribute->values()->create($request->safe()->only('value'));

        return redirect()
            ->route('admin.variant-attributes.show', $variantAttribute)
            ->with('success', 'Đã thêm giá trị thuộc tính.');
    }

    public function edit(VariantValue $variantValue): View
    {
        $variantValue->load('attribute');

        return view('admin.variant-values.edit', compact('variantValue'));
    }

    public function update(
        UpdateVariantValueRequest $request,
        VariantValue $variantValue
    ): RedirectResponse {
        $variantValue->update($request->safe()->only('value'));

        return redirect()
            ->route('admin.variant-attributes.show', $variantValue->attribute_id)
            ->with('success', 'Đã cập nhật giá trị thuộc tính.');
    }

    public function destroy(VariantValue $variantValue): RedirectResponse
    {
        if ($variantValue->variants()->exists()) {
            return back()->with(
                'error',
                'Không thể xóa giá trị vì đang được dùng trong biến thể sản phẩm.'
            );
        }

        $attributeId = $variantValue->attribute_id;
        $variantValue->delete();

        return redirect()
            ->route('admin.variant-attributes.show', $attributeId)
            ->with('success', 'Đã xóa giá trị thuộc tính.');
    }
}
