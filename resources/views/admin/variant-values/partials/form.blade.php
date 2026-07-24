@if ($errors->any())
<div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"><ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
<div><label for="value" class="mb-1.5 block text-sm font-semibold text-gray-700">Giá trị <span class="text-red-500">*</span></label><input id="value" name="value" required maxlength="100" value="{{ old('value', $variantValue->value ?? '') }}" placeholder="Ví dụ: 30 ml, Đỏ, Da dầu..." class="w-full rounded-lg border-gray-300 text-sm focus:border-pink-500 focus:ring-pink-500"></div>
<div class="mt-6 flex gap-3"><button class="rounded-lg bg-pink-600 px-5 py-2.5 text-sm font-semibold text-white">Lưu</button><a href="{{ route('admin.variant-attributes.show', $variantAttribute ?? $variantValue->attribute_id) }}" class="rounded-lg border px-5 py-2.5 text-sm font-semibold">Hủy</a></div>
