<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class BannerController extends Controller
{
    /**
     * Danh sách banner.
     */
    public function index(Request $request): View
    {
        $query = DB::table('banners as b')
            ->leftJoin(
                'users as creator',
                'b.created_by',
                '=',
                'creator.id'
            )
            ->whereNull('b.deleted_at')
            ->select([
                'b.id',
                'b.name',
                'b.title',
                'b.subtitle',
                'b.desktop_image',
                'b.mobile_image',
                'b.link_url',
                'b.button_text',
                'b.position',
                'b.target',
                'b.sort_order',
                'b.status',
                'b.start_at',
                'b.end_at',
                'b.created_by',
                'b.updated_by',
                'b.created_at',
                'b.updated_at',
                'creator.name as creator_name',
            ]);

        $keyword = trim((string) $request->input('keyword'));

        if ($keyword !== '') {
            $query->where(
                function (Builder $builder) use ($keyword): void {
                    $builder
                        ->where(
                            'b.name',
                            'like',
                            '%' . $keyword . '%'
                        )
                        ->orWhere(
                            'b.title',
                            'like',
                            '%' . $keyword . '%'
                        )
                        ->orWhere(
                            'b.subtitle',
                            'like',
                            '%' . $keyword . '%'
                        )
                        ->orWhere(
                            'b.link_url',
                            'like',
                            '%' . $keyword . '%'
                        );
                }
            );
        }

        if ($request->filled('position')) {
            $query->where(
                'b.position',
                $request->input('position')
            );
        }

        if ($request->filled('status')) {
            $query->where(
                'b.status',
                (int) $request->input('status')
            );
        }

        if ($request->filled('target')) {
            $query->where(
                'b.target',
                $request->input('target')
            );
        }

        match ($request->input('validity')) {
            'active' => $query
                ->where('b.status', 1)
                ->where(function (Builder $builder): void {
                    $builder
                        ->whereNull('b.start_at')
                        ->orWhere('b.start_at', '<=', now());
                })
                ->where(function (Builder $builder): void {
                    $builder
                        ->whereNull('b.end_at')
                        ->orWhere('b.end_at', '>=', now());
                }),

            'scheduled' => $query
                ->whereNotNull('b.start_at')
                ->where('b.start_at', '>', now()),

            'expired' => $query
                ->whereNotNull('b.end_at')
                ->where('b.end_at', '<', now()),

            default => null,
        };

        match ($request->input('sort')) {
            'oldest' => $query->orderBy('b.id'),
            'name_asc' => $query->orderBy('b.name'),
            'name_desc' => $query->orderByDesc('b.name'),
            'sort_asc' => $query
                ->orderBy('b.sort_order')
                ->orderByDesc('b.id'),
            'sort_desc' => $query
                ->orderByDesc('b.sort_order')
                ->orderByDesc('b.id'),
            'start_desc' => $query->orderByDesc('b.start_at'),
            'end_asc' => $query->orderBy('b.end_at'),
            default => $query
                ->orderBy('b.sort_order')
                ->orderByDesc('b.id'),
        };

        $banners = $query
            ->paginate(20)
            ->withQueryString();

        $statistics = [
            'total' => DB::table('banners')
                ->whereNull('deleted_at')
                ->count(),

            'active' => DB::table('banners')
                ->whereNull('deleted_at')
                ->where('status', 1)
                ->where(function (Builder $builder): void {
                    $builder
                        ->whereNull('start_at')
                        ->orWhere('start_at', '<=', now());
                })
                ->where(function (Builder $builder): void {
                    $builder
                        ->whereNull('end_at')
                        ->orWhere('end_at', '>=', now());
                })
                ->count(),

            'scheduled' => DB::table('banners')
                ->whereNull('deleted_at')
                ->whereNotNull('start_at')
                ->where('start_at', '>', now())
                ->count(),

            'expired' => DB::table('banners')
                ->whereNull('deleted_at')
                ->whereNotNull('end_at')
                ->where('end_at', '<', now())
                ->count(),

            'home_slider' => DB::table('banners')
                ->whereNull('deleted_at')
                ->where('position', 'home_slider')
                ->count(),

            'popup' => DB::table('banners')
                ->whereNull('deleted_at')
                ->where('position', 'popup')
                ->count(),
        ];

        return view('admin.banners.index', [
            'banners' => $banners,
            'statistics' => $statistics,
            'positions' => $this->positions(),
        ]);
    }

    /**
     * Form tạo banner.
     */
    public function create(): View
    {
        return view('admin.banners.create', [
            'positions' => $this->positions(),
            'targets' => $this->targets(),
        ]);
    }

    /**
     * Lưu banner.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate(
            $this->validationRules(),
            $this->validationMessages()
        );

        $this->validateSchedule($validated);

        $desktopPath = null;
        $mobilePath = null;

        DB::beginTransaction();

        try {
            $desktopPath = $request
                ->file('desktop_image')
                ->store('banners/desktop', 'public');

            if ($request->hasFile('mobile_image')) {
                $mobilePath = $request
                    ->file('mobile_image')
                    ->store('banners/mobile', 'public');
            }

            $bannerId = DB::table('banners')->insertGetId([
                ...$this->payload($validated),
                'desktop_image' => $desktopPath,
                'mobile_image' => $mobilePath,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return redirect()
                ->route('admin.banners.show', $bannerId)
                ->with('success', 'Thêm banner thành công.');
        } catch (Throwable $exception) {
            DB::rollBack();

            if ($desktopPath) {
                Storage::disk('public')->delete($desktopPath);
            }

            if ($mobilePath) {
                Storage::disk('public')->delete($mobilePath);
            }

            if ($exception instanceof ValidationException) {
                throw $exception;
            }

            report($exception);

            return back()
                ->withInput()
                ->with(
                    'error',
                    app()->isLocal()
                        ? 'Lỗi: ' . $exception->getMessage()
                        : 'Không thể thêm banner.'
                );
        }
    }

    /**
     * Chi tiết banner.
     */
    public function show(int $banner): View
    {
        $bannerDetail = DB::table('banners as b')
            ->leftJoin(
                'users as creator',
                'b.created_by',
                '=',
                'creator.id'
            )
            ->leftJoin(
                'users as updater',
                'b.updated_by',
                '=',
                'updater.id'
            )
            ->where('b.id', $banner)
            ->whereNull('b.deleted_at')
            ->select([
                'b.*',
                'creator.name as creator_name',
                'creator.email as creator_email',
                'updater.name as updater_name',
                'updater.email as updater_email',
            ])
            ->first();

        abort_if(! $bannerDetail, 404);

        return view('admin.banners.show', [
            'banner' => $bannerDetail,
            'positions' => $this->positions(),
        ]);
    }

    /**
     * Form sửa banner.
     */
    public function edit(int $banner): View
    {
        $bannerDetail = DB::table('banners')
            ->where('id', $banner)
            ->whereNull('deleted_at')
            ->first();

        abort_if(! $bannerDetail, 404);

        return view('admin.banners.edit', [
            'banner' => $bannerDetail,
            'positions' => $this->positions(),
            'targets' => $this->targets(),
        ]);
    }

    /**
     * Cập nhật banner.
     */
    public function update(
        Request $request,
        int $banner
    ): RedirectResponse {
        $bannerDetail = DB::table('banners')
            ->where('id', $banner)
            ->whereNull('deleted_at')
            ->first();

        abort_if(! $bannerDetail, 404);

        $validated = $request->validate(
            $this->validationRules(false),
            $this->validationMessages()
        );

        $this->validateSchedule($validated);

        $newDesktopPath = null;
        $newMobilePath = null;

        DB::beginTransaction();

        try {
            $desktopPath = $bannerDetail->desktop_image;
            $mobilePath = $bannerDetail->mobile_image;

            if ($request->hasFile('desktop_image')) {
                $newDesktopPath = $request
                    ->file('desktop_image')
                    ->store('banners/desktop', 'public');

                $desktopPath = $newDesktopPath;
            }

            if ($request->boolean('delete_mobile_image')) {
                $mobilePath = null;
            }

            if ($request->hasFile('mobile_image')) {
                $newMobilePath = $request
                    ->file('mobile_image')
                    ->store('banners/mobile', 'public');

                $mobilePath = $newMobilePath;
            }

            DB::table('banners')
                ->where('id', $bannerDetail->id)
                ->update([
                    ...$this->payload($validated),
                    'desktop_image' => $desktopPath,
                    'mobile_image' => $mobilePath,
                    'updated_by' => auth()->id(),
                    'updated_at' => now(),
                ]);

            DB::commit();

            if (
                $newDesktopPath
                && $bannerDetail->desktop_image
                && $bannerDetail->desktop_image !== $newDesktopPath
            ) {
                Storage::disk('public')
                    ->delete($bannerDetail->desktop_image);
            }

            if (
                (
                    $newMobilePath
                    || $request->boolean('delete_mobile_image')
                )
                && $bannerDetail->mobile_image
                && $bannerDetail->mobile_image !== $newMobilePath
            ) {
                Storage::disk('public')
                    ->delete($bannerDetail->mobile_image);
            }

            return redirect()
                ->route('admin.banners.show', $bannerDetail->id)
                ->with('success', 'Cập nhật banner thành công.');
        } catch (Throwable $exception) {
            DB::rollBack();

            if ($newDesktopPath) {
                Storage::disk('public')->delete($newDesktopPath);
            }

            if ($newMobilePath) {
                Storage::disk('public')->delete($newMobilePath);
            }

            if ($exception instanceof ValidationException) {
                throw $exception;
            }

            report($exception);

            return back()
                ->withInput()
                ->with(
                    'error',
                    app()->isLocal()
                        ? 'Lỗi: ' . $exception->getMessage()
                        : 'Không thể cập nhật banner.'
                );
        }
    }

    /**
     * Xóa mềm banner.
     */
    public function destroy(int $banner): RedirectResponse
    {
        $bannerDetail = DB::table('banners')
            ->where('id', $banner)
            ->whereNull('deleted_at')
            ->first([
                'id',
                'name',
            ]);

        abort_if(! $bannerDetail, 404);

        try {
            $now = now();

            DB::table('banners')
                ->where('id', $bannerDetail->id)
                ->update([
                    'status' => 0,
                    'deleted_at' => $now,
                    'updated_by' => auth()->id(),
                    'updated_at' => $now,
                ]);

            return redirect()
                ->route('admin.banners.index')
                ->with(
                    'success',
                    'Đã xóa banner "' . $bannerDetail->name . '".'
                );
        } catch (Throwable $exception) {
            report($exception);

            return back()->with(
                'error',
                app()->isLocal()
                    ? 'Lỗi xóa banner: ' . $exception->getMessage()
                    : 'Không thể xóa banner.'
            );
        }
    }

    /**
     * Validation.
     */
    private function validationRules(
        bool $desktopRequired = true
    ): array {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'title' => [
                'nullable',
                'string',
                'max:255',
            ],

            'subtitle' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'desktop_image' => [
                $desktopRequired ? 'required' : 'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:10240',
            ],

            'mobile_image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:10240',
            ],

            'delete_mobile_image' => [
                'nullable',
                'boolean',
            ],

            'link_url' => [
                'nullable',
                'string',
                'max:2048',
            ],

            'button_text' => [
                'nullable',
                'string',
                'max:100',
            ],

            'position' => [
                'required',
                Rule::in(array_keys($this->positions())),
            ],

            'target' => [
                'required',
                Rule::in(array_keys($this->targets())),
            ],

            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'status' => [
                'nullable',
                'boolean',
            ],

            'start_at' => [
                'nullable',
                'date',
            ],

            'end_at' => [
                'nullable',
                'date',
            ],
        ];
    }

    /**
     * Thông báo validation.
     */
    private function validationMessages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập tên banner.',
            'desktop_image.required' =>
                'Vui lòng chọn ảnh desktop.',
            'desktop_image.image' =>
                'Ảnh desktop không hợp lệ.',
            'desktop_image.max' =>
                'Ảnh desktop không được lớn hơn 10 MB.',
            'mobile_image.image' =>
                'Ảnh mobile không hợp lệ.',
            'mobile_image.max' =>
                'Ảnh mobile không được lớn hơn 10 MB.',
            'position.required' =>
                'Vui lòng chọn vị trí banner.',
            'position.in' =>
                'Vị trí banner không hợp lệ.',
            'target.required' =>
                'Vui lòng chọn cách mở liên kết.',
            'sort_order.min' =>
                'Thứ tự hiển thị không được nhỏ hơn 0.',
        ];
    }

    /**
     * Kiểm tra thời gian.
     */
    private function validateSchedule(
        array $validated
    ): void {
        if (
            ! empty($validated['start_at'])
            && ! empty($validated['end_at'])
            && strtotime($validated['end_at'])
                <= strtotime($validated['start_at'])
        ) {
            throw ValidationException::withMessages([
                'end_at' =>
                    'Thời gian kết thúc phải sau thời gian bắt đầu.',
            ]);
        }
    }

    /**
     * Payload dùng chung.
     */
    private function payload(array $validated): array
    {
        return [
            'name' => trim($validated['name']),
            'title' => $this->nullableTrim(
                $validated['title'] ?? null
            ),
            'subtitle' => $this->nullableTrim(
                $validated['subtitle'] ?? null
            ),
            'link_url' => $this->nullableTrim(
                $validated['link_url'] ?? null
            ),
            'button_text' => $this->nullableTrim(
                $validated['button_text'] ?? null
            ),
            'position' => $validated['position'],
            'target' => $validated['target'],
            'sort_order' => (int) (
                $validated['sort_order'] ?? 0
            ),
            'status' => (int) (
                $validated['status'] ?? 1
            ),
            'start_at' => $validated['start_at'] ?? null,
            'end_at' => $validated['end_at'] ?? null,
        ];
    }

    /**
     * Danh sách vị trí.
     */
    private function positions(): array
    {
        return [
            'home_slider' => 'Slider trang chủ',
            'home_middle' => 'Giữa trang chủ',
            'home_bottom' => 'Cuối trang chủ',
            'category' => 'Trang danh mục',
            'product' => 'Trang sản phẩm',
            'popup' => 'Popup',
        ];
    }

    /**
     * Danh sách target.
     */
    private function targets(): array
    {
        return [
            '_self' => 'Mở trong cùng tab',
            '_blank' => 'Mở tab mới',
        ];
    }

    /**
     * Chuẩn hóa chuỗi nullable.
     */
    private function nullableTrim(
        mixed $value
    ): ?string {
        $value = trim((string) $value);

        return $value !== ''
            ? $value
            : null;
    }
}
