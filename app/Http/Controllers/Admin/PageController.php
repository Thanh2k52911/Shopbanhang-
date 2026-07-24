<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePageRequest;
use App\Http\Requests\Admin\UpdatePageRequest;
use App\Models\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

class PageController extends Controller
{
    public function index(Request $request): View
    {
        $pages = Page::query()
            ->with(['creator:id,name', 'updater:id,name'])
            ->when(
                trim((string) $request->input('keyword')) !== '',
                function ($query) use ($request): void {
                    $keyword = trim((string) $request->input('keyword'));

                    $query->where(function ($builder) use ($keyword): void {
                        $builder
                            ->where('title', 'like', "%{$keyword}%")
                            ->orWhere('slug', 'like', "%{$keyword}%")
                            ->orWhere('meta_title', 'like', "%{$keyword}%");
                    });
                }
            )
            ->when(
                $request->filled('page_type'),
                fn ($query) => $query->where('page_type', $request->string('page_type')->toString())
            )
            ->when(
                $request->filled('status'),
                fn ($query) => $query->where('status', (int) $request->input('status'))
            )
            ->when(
                $request->input('placement') === 'header',
                fn ($query) => $query->where('show_in_header', true)
            )
            ->when(
                $request->input('placement') === 'footer',
                fn ($query) => $query->where('show_in_footer', true)
            )
            ->when(
                $request->input('placement') === 'none',
                fn ($query) => $query
                    ->where('show_in_header', false)
                    ->where('show_in_footer', false)
            )
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $statistics = [
            'total' => Page::query()->count(),
            'active' => Page::query()->where('status', true)->count(),
            'inactive' => Page::query()->where('status', false)->count(),
            'header' => Page::query()->where('show_in_header', true)->count(),
            'footer' => Page::query()->where('show_in_footer', true)->count(),
        ];

        return view('admin.pages.index', [
            'pages' => $pages,
            'statistics' => $statistics,
            'pageTypes' => $this->pageTypes(),
        ]);
    }

    public function create(): View
    {
        return view('admin.pages.create', [
            'pageTypes' => $this->pageTypes(),
        ]);
    }

    public function store(StorePageRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $thumbnail = null;

        DB::beginTransaction();

        try {
            if ($request->hasFile('thumbnail')) {
                $thumbnail = $request->file('thumbnail')->store('pages', 'public');
            }

            $page = Page::query()->create([
                ...$this->payload($validated),
                'thumbnail' => $thumbnail,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            DB::commit();

            return redirect()
                ->route('admin.pages.show', $page)
                ->with('success', 'Tạo trang nội dung thành công.');
        } catch (Throwable $exception) {
            DB::rollBack();

            if ($thumbnail) {
                Storage::disk('public')->delete($thumbnail);
            }

            report($exception);

            return back()
                ->withInput()
                ->with('error', 'Không thể tạo trang nội dung.');
        }
    }

    public function show(Page $page): View
    {
        $page->load(['creator:id,name', 'updater:id,name']);

        return view('admin.pages.show', [
            'page' => $page,
            'pageTypes' => $this->pageTypes(),
        ]);
    }

    public function edit(Page $page): View
    {
        return view('admin.pages.edit', [
            'page' => $page,
            'pageTypes' => $this->pageTypes(),
        ]);
    }

    public function update(UpdatePageRequest $request, Page $page): RedirectResponse
    {
        $validated = $request->validated();
        $oldThumbnail = $page->thumbnail;
        $newThumbnail = null;

        DB::beginTransaction();

        try {
            if ($request->boolean('remove_thumbnail')) {
                $page->thumbnail = null;
            }

            if ($request->hasFile('thumbnail')) {
                $newThumbnail = $request->file('thumbnail')->store('pages', 'public');
                $page->thumbnail = $newThumbnail;
            }

            $page->fill([
                ...$this->payload($validated),
                'updated_by' => auth()->id(),
            ]);
            $page->save();

            DB::commit();

            if (($newThumbnail || $request->boolean('remove_thumbnail')) && $oldThumbnail) {
                Storage::disk('public')->delete($oldThumbnail);
            }

            return redirect()
                ->route('admin.pages.show', $page)
                ->with('success', 'Cập nhật trang nội dung thành công.');
        } catch (Throwable $exception) {
            DB::rollBack();

            if ($newThumbnail) {
                Storage::disk('public')->delete($newThumbnail);
            }

            report($exception);

            return back()
                ->withInput()
                ->with('error', 'Không thể cập nhật trang nội dung.');
        }
    }

    public function destroy(Page $page): RedirectResponse
    {
        try {
            $page->delete();

            return redirect()
                ->route('admin.pages.index')
                ->with('success', 'Đã chuyển trang nội dung vào thùng rác.');
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', 'Không thể xóa trang nội dung.');
        }
    }

    private function payload(array $validated): array
    {
        return [
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'content' => $validated['content'] ?? null,
            'page_type' => $validated['page_type'],
            'meta_title' => $validated['meta_title'] ?? null,
            'meta_description' => $validated['meta_description'] ?? null,
            'meta_keywords' => $validated['meta_keywords'] ?? null,
            'template' => $validated['template'] ?? null,
            'show_in_header' => (bool) ($validated['show_in_header'] ?? false),
            'show_in_footer' => (bool) ($validated['show_in_footer'] ?? false),
            'status' => (bool) ($validated['status'] ?? false),
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
        ];
    }

    private function pageTypes(): array
    {
        return [
            'normal' => 'Trang nội dung thường',
            'policy' => 'Chính sách',
            'guide' => 'Hướng dẫn',
            'about' => 'Giới thiệu',
        ];
    }
}
