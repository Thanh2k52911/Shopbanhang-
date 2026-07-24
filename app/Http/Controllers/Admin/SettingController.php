<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class SettingController extends Controller
{
    /**
     * Hiển thị toàn bộ cấu hình hệ thống theo nhóm.
     */
    public function index(Request $request): View
    {
        $activeGroup = trim(
            (string) $request->input('group', 'general')
        );

        $settings = DB::table('settings')
            ->orderBy('group')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get([
                'id',
                'group',
                'key',
                'value',
                'type',
                'label',
                'description',
                'is_public',
                'sort_order',
                'created_at',
                'updated_at',
            ]);

        $groupedSettings = $settings->groupBy('group');

        if (
            ! $groupedSettings->has($activeGroup)
            && $groupedSettings->isNotEmpty()
        ) {
            $activeGroup = (string) $groupedSettings
                ->keys()
                ->first();
        }

        $statistics = [
            'total' => $settings->count(),
            'groups' => $groupedSettings->count(),
            'public' => $settings
                ->where('is_public', true)
                ->count(),
            'private' => $settings
                ->where('is_public', false)
                ->count(),
            'empty' => $settings
                ->filter(
                    fn ($setting): bool =>
                        $setting->value === null
                        || trim((string) $setting->value) === ''
                )
                ->count(),
        ];

        return view('admin.settings.index', [
            'groupedSettings' => $groupedSettings,
            'activeGroup' => $activeGroup,
            'statistics' => $statistics,
            'groupLabels' => $this->groupLabels(),
            'supportedTypes' => $this->supportedTypes(),
        ]);
    }

    /**
     * Cập nhật toàn bộ cấu hình trong một nhóm.
     */
    public function updateGroup(
        Request $request,
        string $group
    ): RedirectResponse {
        $group = trim($group);

        $settings = DB::table('settings')
            ->where('group', $group)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get([
                'id',
                'group',
                'key',
                'value',
                'type',
                'label',
                'description',
                'is_public',
                'sort_order',
            ]);

        abort_if($settings->isEmpty(), 404);

        $rules = [];
        $messages = [];

        foreach ($settings as $setting) {
            $field = 'settings.' . $setting->key;

            $rules[$field] = $this->rulesForType(
                (string) $setting->type
            );

            $label = $setting->label ?: $setting->key;

            $messages[$field . '.image'] =
                $label . ' phải là file ảnh hợp lệ.';

            $messages[$field . '.file'] =
                $label . ' phải là file hợp lệ.';

            $messages[$field . '.max'] =
                $label . ' vượt quá dung lượng hoặc độ dài cho phép.';

            $messages[$field . '.numeric'] =
                $label . ' phải là một số hợp lệ.';

            $messages[$field . '.boolean'] =
                $label . ' phải là giá trị bật hoặc tắt.';

            $messages[$field . '.json'] =
                $label . ' phải là JSON hợp lệ.';
        }

        $validated = $request->validate(
            $rules,
            $messages
        );

        $submittedSettings = $validated['settings'] ?? [];
        $newFilePaths = [];
        $oldFilePathsToDelete = [];

        DB::beginTransaction();

        try {
            foreach ($settings as $setting) {
                $key = (string) $setting->key;
                $type = (string) $setting->type;

                $submittedValue = $submittedSettings[$key] ?? null;

                $newValue = $this->normalizeValue(
                    $type,
                    $submittedValue,
                    $setting->value,
                    $newFilePaths,
                    $oldFilePathsToDelete
                );

                DB::table('settings')
                    ->where('id', $setting->id)
                    ->update([
                        'value' => $newValue,
                        'updated_at' => now(),
                    ]);
            }

            DB::commit();

            foreach ($oldFilePathsToDelete as $oldPath) {
                if (
                    $oldPath
                    && ! in_array(
                        $oldPath,
                        $newFilePaths,
                        true
                    )
                ) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            $this->clearSettingCaches();

            return redirect()
                ->route(
                    'admin.settings.index',
                    ['group' => $group]
                )
                ->with(
                    'success',
                    'Cập nhật nhóm cấu hình thành công.'
                );
        } catch (Throwable $exception) {
            DB::rollBack();

            foreach ($newFilePaths as $path) {
                Storage::disk('public')->delete($path);
            }

            report($exception);

            return back()
                ->withInput()
                ->with(
                    'error',
                    app()->isLocal()
                        ? 'Lỗi cập nhật cấu hình: '
                            . $exception->getMessage()
                        : 'Không thể cập nhật cấu hình.'
                );
        }
    }

    /**
     * Tạo một cấu hình mới.
     */
    public function store(
        Request $request
    ): RedirectResponse {
        $validated = $request->validate(
            [
                'group' => [
                    'required',
                    'string',
                    'max:100',
                    'regex:/^[a-z0-9_\-]+$/',
                ],

                'key' => [
                    'required',
                    'string',
                    'max:255',
                    'regex:/^[a-z0-9_\-\.]+$/',
                    Rule::unique('settings', 'key'),
                ],

                'type' => [
                    'required',
                    Rule::in(
                        array_keys(
                            $this->supportedTypes()
                        )
                    ),
                ],

                'label' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'description' => [
                    'nullable',
                    'string',
                    'max:5000',
                ],

                'value' => [
                    'nullable',
                ],

                'is_public' => [
                    'nullable',
                    'boolean',
                ],

                'sort_order' => [
                    'required',
                    'integer',
                    'min:0',
                ],
            ],
            [
                'group.required' =>
                    'Vui lòng nhập nhóm cấu hình.',

                'group.regex' =>
                    'Nhóm chỉ được chứa chữ thường, số, dấu gạch ngang và gạch dưới.',

                'key.required' =>
                    'Vui lòng nhập khóa cấu hình.',

                'key.unique' =>
                    'Khóa cấu hình đã tồn tại.',

                'key.regex' =>
                    'Khóa chỉ được chứa chữ thường, số, dấu chấm, gạch ngang và gạch dưới.',

                'type.required' =>
                    'Vui lòng chọn kiểu dữ liệu.',

                'type.in' =>
                    'Kiểu dữ liệu không được hỗ trợ.',
            ]
        );

        $value = $this->normalizeNewSettingValue(
            $request,
            (string) $validated['type']
        );

        try {
            DB::table('settings')->insert([
                'group' => strtolower(
                    trim($validated['group'])
                ),
                'key' => strtolower(
                    trim($validated['key'])
                ),
                'value' => $value,
                'type' => $validated['type'],
                'label' => $this->nullableTrim(
                    $validated['label'] ?? null
                ),
                'description' => $this->nullableTrim(
                    $validated['description'] ?? null
                ),
                'is_public' => (bool) (
                    $validated['is_public'] ?? false
                ),
                'sort_order' => (int) $validated['sort_order'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->clearSettingCaches();

            return redirect()
                ->route(
                    'admin.settings.index',
                    ['group' => $validated['group']]
                )
                ->with(
                    'success',
                    'Tạo cấu hình mới thành công.'
                );
        } catch (Throwable $exception) {
            if (
                $value
                && in_array(
                    $validated['type'],
                    ['image', 'file'],
                    true
                )
            ) {
                Storage::disk('public')->delete($value);
            }

            report($exception);

            return back()
                ->withInput()
                ->with(
                    'error',
                    app()->isLocal()
                        ? 'Lỗi tạo cấu hình: '
                            . $exception->getMessage()
                        : 'Không thể tạo cấu hình.'
                );
        }
    }

    /**
     * Cập nhật metadata của một cấu hình.
     */
    public function updateMeta(
        Request $request,
        int $setting
    ): RedirectResponse {
        $settingDetail = DB::table('settings')
            ->where('id', $setting)
            ->first();

        abort_if(! $settingDetail, 404);

        $validated = $request->validate(
            [
                'group' => [
                    'required',
                    'string',
                    'max:100',
                    'regex:/^[a-z0-9_\-]+$/',
                ],

                'key' => [
                    'required',
                    'string',
                    'max:255',
                    'regex:/^[a-z0-9_\-\.]+$/',
                    Rule::unique(
                        'settings',
                        'key'
                    )->ignore($settingDetail->id),
                ],

                'type' => [
                    'required',
                    Rule::in(
                        array_keys(
                            $this->supportedTypes()
                        )
                    ),
                ],

                'label' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'description' => [
                    'nullable',
                    'string',
                    'max:5000',
                ],

                'is_public' => [
                    'nullable',
                    'boolean',
                ],

                'sort_order' => [
                    'required',
                    'integer',
                    'min:0',
                ],
            ],
            [
                'group.required' =>
                    'Vui lòng nhập nhóm cấu hình.',

                'key.required' =>
                    'Vui lòng nhập khóa cấu hình.',

                'key.unique' =>
                    'Khóa cấu hình đã tồn tại.',

                'type.required' =>
                    'Vui lòng chọn kiểu dữ liệu.',
            ]
        );

        try {
            DB::table('settings')
                ->where('id', $settingDetail->id)
                ->update([
                    'group' => strtolower(
                        trim($validated['group'])
                    ),
                    'key' => strtolower(
                        trim($validated['key'])
                    ),
                    'type' => $validated['type'],
                    'label' => $this->nullableTrim(
                        $validated['label'] ?? null
                    ),
                    'description' => $this->nullableTrim(
                        $validated['description'] ?? null
                    ),
                    'is_public' => (bool) (
                        $validated['is_public'] ?? false
                    ),
                    'sort_order' => (int) $validated[
                        'sort_order'
                    ],
                    'updated_at' => now(),
                ]);

            $this->clearSettingCaches();

            return redirect()
                ->route(
                    'admin.settings.index',
                    ['group' => $validated['group']]
                )
                ->with(
                    'success',
                    'Cập nhật thông tin cấu hình thành công.'
                );
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with(
                    'error',
                    app()->isLocal()
                        ? 'Lỗi cập nhật metadata: '
                            . $exception->getMessage()
                        : 'Không thể cập nhật thông tin cấu hình.'
                );
        }
    }

    /**
     * Xóa một cấu hình.
     */
    public function destroy(
        int $setting
    ): RedirectResponse {
        $settingDetail = DB::table('settings')
            ->where('id', $setting)
            ->first();

        abort_if(! $settingDetail, 404);

        $group = (string) $settingDetail->group;

        DB::beginTransaction();

        try {
            DB::table('settings')
                ->where('id', $settingDetail->id)
                ->delete();

            DB::commit();

            if (
                in_array(
                    $settingDetail->type,
                    ['image', 'file'],
                    true
                )
                && $settingDetail->value
            ) {
                Storage::disk('public')->delete(
                    $settingDetail->value
                );
            }

            $this->clearSettingCaches();

            return redirect()
                ->route(
                    'admin.settings.index',
                    ['group' => $group]
                )
                ->with(
                    'success',
                    'Đã xóa cấu hình.'
                );
        } catch (Throwable $exception) {
            DB::rollBack();

            report($exception);

            return back()->with(
                'error',
                app()->isLocal()
                    ? 'Lỗi xóa cấu hình: '
                        . $exception->getMessage()
                    : 'Không thể xóa cấu hình.'
            );
        }
    }

    /**
     * Khôi phục dữ liệu mẫu mặc định cho những key chưa tồn tại.
     */
    public function seedDefaults(): RedirectResponse
    {
        $defaults = $this->defaultSettings();

        try {
            DB::transaction(
                function () use ($defaults): void {
                    foreach ($defaults as $setting) {
                        DB::table('settings')
                            ->updateOrInsert(
                                ['key' => $setting['key']],
                                [
                                    'group' => $setting['group'],
                                    'value' => $setting['value'],
                                    'type' => $setting['type'],
                                    'label' => $setting['label'],
                                    'description' => $setting[
                                        'description'
                                    ],
                                    'is_public' => $setting[
                                        'is_public'
                                    ],
                                    'sort_order' => $setting[
                                        'sort_order'
                                    ],
                                    'updated_at' => now(),
                                    'created_at' => now(),
                                ]
                            );
                    }
                }
            );

            $this->clearSettingCaches();

            return back()->with(
                'success',
                'Đã bổ sung bộ cấu hình mặc định.'
            );
        } catch (Throwable $exception) {
            report($exception);

            return back()->with(
                'error',
                app()->isLocal()
                    ? 'Lỗi tạo cấu hình mặc định: '
                        . $exception->getMessage()
                    : 'Không thể tạo cấu hình mặc định.'
            );
        }
    }

    /**
     * Xây dựng rule theo type trong bảng settings.
     */
    private function rulesForType(
        string $type
    ): array {
        return match ($type) {
            'number' => [
                'nullable',
                'numeric',
            ],

            'boolean' => [
                'nullable',
                'boolean',
            ],

            'json' => [
                'nullable',
                'json',
            ],

            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp,gif,svg,ico',
                'max:5120',
            ],

            'file' => [
                'nullable',
                'file',
                'max:10240',
            ],

            'color' => [
                'nullable',
                'string',
                'max:30',
            ],

            'text' => [
                'nullable',
                'string',
                'max:50000',
            ],

            default => [
                'nullable',
                'string',
                'max:5000',
            ],
        };
    }

    /**
     * Chuẩn hóa giá trị khi cập nhật một nhóm.
     *
     * @param array<int, string> $newFilePaths
     * @param array<int, string> $oldFilePathsToDelete
     */
    private function normalizeValue(
        string $type,
        mixed $submittedValue,
        mixed $oldValue,
        array &$newFilePaths,
        array &$oldFilePathsToDelete
    ): ?string {
        if (in_array($type, ['image', 'file'], true)) {
            if (! $submittedValue instanceof UploadedFile) {
                return $oldValue !== null
                    ? (string) $oldValue
                    : null;
            }

            $directory = $type === 'image'
                ? 'settings/images'
                : 'settings/files';

            $newPath = $submittedValue->store(
                $directory,
                'public'
            );

            $newFilePaths[] = $newPath;

            if ($oldValue) {
                $oldFilePathsToDelete[] = (string) $oldValue;
            }

            return $newPath;
        }

        return match ($type) {
            'boolean' => $submittedValue
                ? '1'
                : '0',

            'number' => $submittedValue === null
                || $submittedValue === ''
                    ? null
                    : (string) $submittedValue,

            'json' => $submittedValue === null
                || trim((string) $submittedValue) === ''
                    ? null
                    : json_encode(
                        json_decode(
                            (string) $submittedValue,
                            true,
                            512,
                            JSON_THROW_ON_ERROR
                        ),
                        JSON_UNESCAPED_UNICODE
                        | JSON_PRETTY_PRINT
                        | JSON_UNESCAPED_SLASHES
                    ),

            default => $this->nullableTrim($submittedValue),
        };
    }

    /**
     * Chuẩn hóa value của cấu hình mới.
     */
    private function normalizeNewSettingValue(
        Request $request,
        string $type
    ): ?string {
        if (in_array($type, ['image', 'file'], true)) {
            $file = $request->file('value');

            if (! $file) {
                return null;
            }

            $request->validate([
                'value' => $this->rulesForType($type),
            ]);

            return $file->store(
                $type === 'image'
                    ? 'settings/images'
                    : 'settings/files',
                'public'
            );
        }

        $value = $request->input('value');

        if ($type === 'json') {
            if ($value === null || trim((string) $value) === '') {
                return null;
            }

            try {
                return json_encode(
                    json_decode(
                        (string) $value,
                        true,
                        512,
                        JSON_THROW_ON_ERROR
                    ),
                    JSON_UNESCAPED_UNICODE
                    | JSON_PRETTY_PRINT
                    | JSON_UNESCAPED_SLASHES
                );
            } catch (Throwable) {
                throw ValidationException::withMessages([
                    'value' => 'Giá trị JSON không hợp lệ.',
                ]);
            }
        }

        if ($type === 'boolean') {
            return $request->boolean('value')
                ? '1'
                : '0';
        }

        return $this->nullableTrim($value);
    }

    private function clearSettingCaches(): void
    {
        app(SettingService::class)->clearCache();
    }

    private function groupLabels(): array
    {
        return [
            'general' => 'Chung',
            'contact' => 'Liên hệ',
            'social' => 'Mạng xã hội',
            'payment' => 'Thanh toán',
            'shipping' => 'Vận chuyển',
            'seo' => 'SEO',
            'email' => 'Email',
            'shop' => 'Cửa hàng',
            'system' => 'Hệ thống',
        ];
    }

    private function supportedTypes(): array
    {
        return [
            'string' => 'Chuỗi ngắn',
            'text' => 'Văn bản dài',
            'number' => 'Số',
            'boolean' => 'Bật / tắt',
            'json' => 'JSON',
            'image' => 'Hình ảnh',
            'file' => 'Tệp',
            'color' => 'Màu sắc',
        ];
    }

    /**
     * Bộ cấu hình mặc định.
     */
    private function defaultSettings(): array
    {
        return [
            [
                'group' => 'general',
                'key' => 'site_name',
                'value' => 'Cosmetic Shop',
                'type' => 'string',
                'label' => 'Tên website',
                'description' =>
                    'Tên thương hiệu hiển thị trên website.',
                'is_public' => true,
                'sort_order' => 10,
            ],
            [
                'group' => 'general',
                'key' => 'site_logo',
                'value' => null,
                'type' => 'image',
                'label' => 'Logo website',
                'description' =>
                    'Logo chính của cửa hàng.',
                'is_public' => true,
                'sort_order' => 20,
            ],
            [
                'group' => 'general',
                'key' => 'site_favicon',
                'value' => null,
                'type' => 'image',
                'label' => 'Favicon',
                'description' =>
                    'Biểu tượng hiển thị trên tab trình duyệt.',
                'is_public' => true,
                'sort_order' => 30,
            ],
            [
                'group' => 'contact',
                'key' => 'contact_hotline',
                'value' => '',
                'type' => 'string',
                'label' => 'Hotline',
                'description' =>
                    'Số điện thoại hỗ trợ khách hàng.',
                'is_public' => true,
                'sort_order' => 10,
            ],
            [
                'group' => 'contact',
                'key' => 'contact_email',
                'value' => '',
                'type' => 'string',
                'label' => 'Email liên hệ',
                'description' =>
                    'Email hỗ trợ hiển thị trên website.',
                'is_public' => true,
                'sort_order' => 20,
            ],
            [
                'group' => 'contact',
                'key' => 'contact_address',
                'value' => '',
                'type' => 'text',
                'label' => 'Địa chỉ',
                'description' =>
                    'Địa chỉ cửa hàng hoặc văn phòng.',
                'is_public' => true,
                'sort_order' => 30,
            ],
            [
                'group' => 'social',
                'key' => 'facebook_url',
                'value' => '',
                'type' => 'string',
                'label' => 'Facebook',
                'description' => 'Đường dẫn Facebook.',
                'is_public' => true,
                'sort_order' => 10,
            ],
            [
                'group' => 'social',
                'key' => 'instagram_url',
                'value' => '',
                'type' => 'string',
                'label' => 'Instagram',
                'description' => 'Đường dẫn Instagram.',
                'is_public' => true,
                'sort_order' => 20,
            ],
            [
                'group' => 'social',
                'key' => 'tiktok_url',
                'value' => '',
                'type' => 'string',
                'label' => 'TikTok',
                'description' => 'Đường dẫn TikTok.',
                'is_public' => true,
                'sort_order' => 30,
            ],
            [
                'group' => 'social',
                'key' => 'youtube_url',
                'value' => '',
                'type' => 'string',
                'label' => 'YouTube',
                'description' => 'Đường dẫn YouTube.',
                'is_public' => true,
                'sort_order' => 40,
            ],
            [
                'group' => 'shipping',
                'key' => 'free_shipping_threshold',
                'value' => '0',
                'type' => 'number',
                'label' => 'Miễn phí vận chuyển từ',
                'description' =>
                    'Giá trị đơn hàng tối thiểu để miễn phí ship.',
                'is_public' => true,
                'sort_order' => 10,
            ],
            [
                'group' => 'seo',
                'key' => 'meta_title',
                'value' => '',
                'type' => 'string',
                'label' => 'Meta title',
                'description' =>
                    'Tiêu đề SEO mặc định.',
                'is_public' => true,
                'sort_order' => 10,
            ],
            [
                'group' => 'seo',
                'key' => 'meta_description',
                'value' => '',
                'type' => 'text',
                'label' => 'Meta description',
                'description' =>
                    'Mô tả SEO mặc định.',
                'is_public' => true,
                'sort_order' => 20,
            ],
            [
                'group' => 'system',
                'key' => 'maintenance_mode',
                'value' => '0',
                'type' => 'boolean',
                'label' => 'Chế độ bảo trì',
                'description' =>
                    'Bật để tạm thời đóng website phía khách hàng.',
                'is_public' => false,
                'sort_order' => 10,
            ],
            [
                'group' => 'system',
                'key' => 'allow_registration',
                'value' => '1',
                'type' => 'boolean',
                'label' => 'Cho phép đăng ký',
                'description' =>
                    'Cho phép khách tạo tài khoản mới.',
                'is_public' => false,
                'sort_order' => 20,
            ],
            [
                'group' => 'system',
                'key' => 'allow_reviews',
                'value' => '1',
                'type' => 'boolean',
                'label' => 'Cho phép đánh giá',
                'description' =>
                    'Cho phép khách hàng gửi đánh giá sản phẩm.',
                'is_public' => false,
                'sort_order' => 30,
            ],
            [
                'group' => 'system',
                'key' => 'allow_questions',
                'value' => '1',
                'type' => 'boolean',
                'label' => 'Cho phép hỏi đáp',
                'description' =>
                    'Cho phép gửi câu hỏi sản phẩm.',
                'is_public' => false,
                'sort_order' => 40,
            ],
        ];
    }

    private function nullableTrim(
        mixed $value
    ): ?string {
        $value = trim((string) $value);

        return $value !== ''
            ? $value
            : null;
    }
}
