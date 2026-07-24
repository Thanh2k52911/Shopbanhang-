<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $query = DB::table('categories as c')
            ->leftJoin('categories as parent', 'c.parent_id', '=', 'parent.id')
            ->whereNull('c.deleted_at')
            ->select([
                'c.id', 'c.parent_id', 'c.name', 'c.slug', 'c.thumbnail',
                'c.description', 'c.sort_order', 'c.status', 'c.created_at',
                'c.updated_at', 'parent.name as parent_name',
            ])
            ->selectSub(function (Builder $builder): void {
                $builder->from('products')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('products.category_id', 'c.id')
                    ->whereNull('products.deleted_at');
            }, 'products_count')
            ->selectSub(function (Builder $builder): void {
                $builder->from('categories as children')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('children.parent_id', 'c.id')
                    ->whereNull('children.deleted_at');
            }, 'children_count');

        $keyword = trim((string) $request->input('keyword'));

        if ($keyword !== '') {
            $query->where(function (Builder $builder) use ($keyword): void {
                $builder->where('c.name', 'like', '%' . $keyword . '%')
                    ->orWhere('c.slug', 'like', '%' . $keyword . '%')
                    ->orWhere('parent.name', 'like', '%' . $keyword . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('c.status', (int) $request->input('status'));
        }

        if ($request->filled('parent_id')) {
            if ($request->input('parent_id') === 'root') {
                $query->whereNull('c.parent_id');
            } else {
                $query->where('c.parent_id', (int) $request->input('parent_id'));
            }
        }

        match ($request->input('sort')) {
            'oldest' => $query->orderBy('c.id'),
            'name_asc' => $query->orderBy('c.name'),
            'name_desc' => $query->orderByDesc('c.name'),
            'sort_desc' => $query->orderByDesc('c.sort_order')->orderBy('c.name'),
            default => $query->orderBy('c.sort_order')->orderByDesc('c.id'),
        };

        $categories = $query->paginate(20)->withQueryString();

        $parentCategories = DB::table('categories')
            ->whereNull('deleted_at')
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name']);

        $statistics = [
            'total' => DB::table('categories')->whereNull('deleted_at')->count(),
            'active' => DB::table('categories')->whereNull('deleted_at')->where('status', 1)->count(),
            'inactive' => DB::table('categories')->whereNull('deleted_at')->where('status', 0)->count(),
            'root' => DB::table('categories')->whereNull('deleted_at')->whereNull('parent_id')->count(),
            'children' => DB::table('categories')->whereNull('deleted_at')->whereNotNull('parent_id')->count(),
        ];

        return view('admin.categories.index', compact(
            'categories', 'parentCategories', 'statistics'
        ));
    }

    public function create(): View
    {
        $parentCategories = DB::table('categories')
            ->whereNull('deleted_at')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'parent_id', 'name']);

        return view('admin.categories.create', compact('parentCategories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'parent_id' => ['nullable', 'integer', Rule::exists('categories', 'id')->whereNull('deleted_at')],
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:180', Rule::unique('categories', 'slug')],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'integer', Rule::in([0, 1])],
        ], [
            'name.required' => 'Vui lòng nhập tên danh mục.',
            'name.max' => 'Tên danh mục không được vượt quá 150 ký tự.',
            'slug.unique' => 'Slug danh mục đã tồn tại.',
            'thumbnail.image' => 'File thumbnail phải là hình ảnh.',
            'thumbnail.mimes' => 'Thumbnail phải có định dạng JPG, JPEG, PNG hoặc WEBP.',
            'thumbnail.max' => 'Thumbnail không được lớn hơn 5 MB.',
        ]);

        $storedThumbnailPath = null;
        DB::beginTransaction();

        try {
            $slug = trim((string) ($validated['slug'] ?? ''));
            $slug = $slug !== '' ? Str::slug($slug) : Str::slug($validated['name']);
            $baseSlug = $slug !== '' ? $slug : 'danh-muc';
            $slug = $baseSlug;
            $number = 1;

            while (DB::table('categories')->where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $number;
                $number++;
            }

            if ($request->hasFile('thumbnail')) {
                $storedThumbnailPath = $request->file('thumbnail')->store('categories', 'public');
            }

            $now = now();
            $categoryId = DB::table('categories')->insertGetId([
                'parent_id' => !empty($validated['parent_id']) ? (int) $validated['parent_id'] : null,
                'name' => trim($validated['name']),
                'slug' => $slug,
                'thumbnail' => $storedThumbnailPath,
                'description' => $validated['description'] ?? null,
                'sort_order' => (int) ($validated['sort_order'] ?? 0),
                'status' => (int) ($validated['status'] ?? 1),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::commit();

            return redirect()->route('admin.categories.show', $categoryId)
                ->with('success', 'Thêm danh mục thành công.');
        } catch (Throwable $exception) {
            DB::rollBack();

            if ($storedThumbnailPath) {
                Storage::disk('public')->delete($storedThumbnailPath);
            }

            report($exception);

            return back()->withInput()->with(
                'error',
                app()->isLocal() ? 'Lỗi: ' . $exception->getMessage() : 'Không thể thêm danh mục.'
            );
        }
    }

    public function show(int $category): View
    {
        $categoryDetail = DB::table('categories as c')
            ->leftJoin('categories as parent', 'c.parent_id', '=', 'parent.id')
            ->where('c.id', $category)
            ->whereNull('c.deleted_at')
            ->select([
                'c.id', 'c.parent_id', 'c.name', 'c.slug', 'c.thumbnail',
                'c.description', 'c.sort_order', 'c.status', 'c.created_at',
                'c.updated_at', 'parent.name as parent_name', 'parent.slug as parent_slug',
            ])
            ->first();

        abort_if(!$categoryDetail, 404);

        $children = DB::table('categories')
            ->where('parent_id', $categoryDetail->id)
            ->whereNull('deleted_at')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'thumbnail', 'sort_order', 'status', 'created_at']);

        $products = DB::table('products')
            ->where('category_id', $categoryDetail->id)
            ->whereNull('deleted_at')
            ->orderByDesc('id')
            ->limit(20)
            ->get(['id', 'name', 'slug', 'status', 'is_featured', 'view_count', 'created_at']);

        $statistics = [
            'children' => $children->count(),
            'active_children' => $children->where('status', 1)->count(),
            'products' => DB::table('products')->where('category_id', $categoryDetail->id)->whereNull('deleted_at')->count(),
            'active_products' => DB::table('products')->where('category_id', $categoryDetail->id)->whereNull('deleted_at')->where('status', 1)->count(),
        ];

        return view('admin.categories.show', [
            'category' => $categoryDetail,
            'children' => $children,
            'products' => $products,
            'statistics' => $statistics,
        ]);
    }

    public function edit(int $category): View
    {
        $categoryDetail = DB::table('categories')
            ->where('id', $category)
            ->whereNull('deleted_at')
            ->first([
                'id', 'parent_id', 'name', 'slug', 'thumbnail', 'description',
                'sort_order', 'status', 'created_at', 'updated_at',
            ]);

        abort_if(!$categoryDetail, 404);

        $excludedIds = $this->descendantIds($categoryDetail->id);
        $excludedIds[] = $categoryDetail->id;

        $parentCategories = DB::table('categories')
            ->whereNull('deleted_at')
            ->whereNotIn('id', $excludedIds)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'parent_id', 'name']);

        return view('admin.categories.edit', [
            'category' => $categoryDetail,
            'parentCategories' => $parentCategories,
        ]);
    }

    public function update(Request $request, int $category): RedirectResponse
    {
        $categoryDetail = DB::table('categories')
            ->where('id', $category)
            ->whereNull('deleted_at')
            ->first();

        abort_if(!$categoryDetail, 404);

        $validated = $request->validate([
            'parent_id' => ['nullable', 'integer', Rule::exists('categories', 'id')->whereNull('deleted_at')],
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:180', Rule::unique('categories', 'slug')->ignore($categoryDetail->id)],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'delete_thumbnail' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'integer', Rule::in([0, 1])],
        ], [
            'name.required' => 'Vui lòng nhập tên danh mục.',
            'name.max' => 'Tên danh mục không được vượt quá 150 ký tự.',
            'slug.unique' => 'Slug danh mục đã tồn tại.',
            'thumbnail.image' => 'File thumbnail phải là hình ảnh.',
            'thumbnail.mimes' => 'Thumbnail phải có định dạng JPG, JPEG, PNG hoặc WEBP.',
            'thumbnail.max' => 'Thumbnail không được lớn hơn 5 MB.',
        ]);

        $parentId = !empty($validated['parent_id']) ? (int) $validated['parent_id'] : null;

        if ($parentId === $categoryDetail->id) {
            throw ValidationException::withMessages([
                'parent_id' => 'Danh mục không thể chọn chính nó làm danh mục cha.',
            ]);
        }

        if ($parentId !== null && in_array($parentId, $this->descendantIds($categoryDetail->id), true)) {
            throw ValidationException::withMessages([
                'parent_id' => 'Không thể chọn danh mục con làm danh mục cha.',
            ]);
        }

        $newThumbnailPath = null;
        $oldThumbnailPath = $categoryDetail->thumbnail;
        $thumbnailPathToDeleteAfterCommit = null;

        DB::beginTransaction();

        try {
            $slug = trim((string) ($validated['slug'] ?? ''));
            $slug = $slug !== '' ? Str::slug($slug) : Str::slug($validated['name']);
            $slug = $slug !== '' ? $slug : 'danh-muc-' . $categoryDetail->id;

            if (DB::table('categories')->where('slug', $slug)->where('id', '<>', $categoryDetail->id)->exists()) {
                throw ValidationException::withMessages(['slug' => 'Slug danh mục đã tồn tại.']);
            }

            $thumbnail = $oldThumbnailPath;

            if ($request->boolean('delete_thumbnail')) {
                $thumbnail = null;
                $thumbnailPathToDeleteAfterCommit = $oldThumbnailPath;
            }

            if ($request->hasFile('thumbnail')) {
                $newThumbnailPath = $request->file('thumbnail')->store('categories', 'public');
                $thumbnail = $newThumbnailPath;
                $thumbnailPathToDeleteAfterCommit = $oldThumbnailPath;
            }

            DB::table('categories')->where('id', $categoryDetail->id)->update([
                'parent_id' => $parentId,
                'name' => trim($validated['name']),
                'slug' => $slug,
                'thumbnail' => $thumbnail,
                'description' => $validated['description'] ?? null,
                'sort_order' => (int) ($validated['sort_order'] ?? 0),
                'status' => (int) ($validated['status'] ?? 1),
                'updated_at' => now(),
            ]);

            DB::commit();

            if ($thumbnailPathToDeleteAfterCommit && $thumbnailPathToDeleteAfterCommit !== $newThumbnailPath) {
                Storage::disk('public')->delete($thumbnailPathToDeleteAfterCommit);
            }

            return redirect()->route('admin.categories.show', $categoryDetail->id)
                ->with('success', 'Cập nhật danh mục thành công.');
        } catch (Throwable $exception) {
            DB::rollBack();

            if ($newThumbnailPath) {
                Storage::disk('public')->delete($newThumbnailPath);
            }

            if ($exception instanceof ValidationException) {
                throw $exception;
            }

            report($exception);

            return back()->withInput()->with(
                'error',
                app()->isLocal() ? 'Lỗi: ' . $exception->getMessage() : 'Không thể cập nhật danh mục.'
            );
        }
    }

    public function destroy(int $category): RedirectResponse
    {
        $categoryDetail = DB::table('categories')
            ->where('id', $category)
            ->whereNull('deleted_at')
            ->first(['id', 'name', 'thumbnail']);

        abort_if(!$categoryDetail, 404);

        $hasChildren = DB::table('categories')
            ->where('parent_id', $categoryDetail->id)
            ->whereNull('deleted_at')
            ->exists();

        if ($hasChildren) {
            return back()->with('error', 'Không thể xóa danh mục này vì vẫn còn danh mục con.');
        }

        $hasProducts = DB::table('products')
            ->where('category_id', $categoryDetail->id)
            ->whereNull('deleted_at')
            ->exists();

        if ($hasProducts) {
            return back()->with('error', 'Không thể xóa danh mục này vì vẫn còn sản phẩm.');
        }

        DB::beginTransaction();

        try {
            $now = now();

            DB::table('categories')
                ->where('id', $categoryDetail->id)
                ->whereNull('deleted_at')
                ->update([
                    'status' => 0,
                    'deleted_at' => $now,
                    'updated_at' => $now,
                ]);

            DB::commit();

            return redirect()->route('admin.categories.index')
                ->with('success', 'Đã xóa danh mục "' . $categoryDetail->name . '".');
        } catch (Throwable $exception) {
            DB::rollBack();
            report($exception);

            return back()->with(
                'error',
                app()->isLocal() ? 'Lỗi xóa danh mục: ' . $exception->getMessage() : 'Không thể xóa danh mục.'
            );
        }
    }

    /** @return array<int> */
    private function descendantIds(int $categoryId): array
    {
        $result = [];
        $pending = [$categoryId];

        while (!empty($pending)) {
            $currentIds = $pending;
            $pending = [];

            $children = DB::table('categories')
                ->whereNull('deleted_at')
                ->whereIn('parent_id', $currentIds)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

            foreach ($children as $childId) {
                if (!in_array($childId, $result, true)) {
                    $result[] = $childId;
                    $pending[] = $childId;
                }
            }
        }

        return $result;
    }
}
