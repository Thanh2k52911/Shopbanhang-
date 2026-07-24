<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route as RouteFacade;
use Illuminate\Support\Facades\Schema;
use Throwable;

class AuditSecurityPerformance extends Command
{
    protected $signature = 'project:audit-security
        {--strict : Trả mã lỗi nếu còn cảnh báo}
        {--skip-db : Bỏ qua kiểm tra index database}';

    protected $description = 'Rà cấu hình bảo mật, middleware route, storage và các dấu hiệu query kém hiệu năng.';

    /** @var list<string> */
    private array $warnings = [];

    /** @var list<string> */
    private array $errors = [];

    public function handle(): int
    {
        $this->newLine();
        $this->info('COSMETICSHOP - KIỂM TRA BẢO MẬT VÀ HIỆU NĂNG');
        $this->newLine();

        $this->checkEnvironment();
        $this->checkStorage();
        $this->checkRoutes();
        $this->scanControllers();

        if (! $this->option('skip-db')) {
            $this->checkDatabaseIndexes();
        }

        $this->printResult();

        if ($this->errors !== []) {
            return self::FAILURE;
        }

        if ($this->option('strict') && $this->warnings !== []) {
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function checkEnvironment(): void
    {
        if (app()->environment('production') && (bool) config('app.debug')) {
            $this->errors[] = 'APP_DEBUG đang bật trong môi trường production.';
        }

        if (app()->environment('production') && ! str_starts_with((string) config('app.url'), 'https://')) {
            $this->warnings[] = 'APP_URL production chưa dùng HTTPS.';
        }

        if (app()->environment('production') && ! (bool) config('session.secure')) {
            $this->warnings[] = 'SESSION_SECURE_COOKIE chưa bật trong production.';
        }

        if ((string) config('session.same_site') === '') {
            $this->warnings[] = 'SESSION_SAME_SITE chưa được cấu hình.';
        }

        if ((string) config('app.key') === '') {
            $this->errors[] = 'APP_KEY đang trống.';
        }

        $this->line('✓ Đã kiểm tra APP_DEBUG, APP_URL, APP_KEY và cookie session.');
    }

    private function checkStorage(): void
    {
        $publicStorage = public_path('storage');

        if (! File::exists($publicStorage)) {
            $this->warnings[] = 'Chưa có public/storage. Chạy: php artisan storage:link';
        }

        foreach ([storage_path('framework'), storage_path('logs')] as $path) {
            if (! File::isWritable($path)) {
                $this->errors[] = "Thư mục không có quyền ghi: {$path}";
            }
        }

        $this->line('✓ Đã kiểm tra storage link và quyền ghi thư mục runtime.');
    }

    private function checkRoutes(): void
    {
        /** @var Route $route */
        foreach (RouteFacade::getRoutes() as $route) {
            $name = (string) $route->getName();
            $uri = $route->uri();
            $methods = $route->methods();
            $middleware = $route->gatherMiddleware();

            if (str_starts_with($name, 'admin.') || str_starts_with($uri, 'admin/')) {
                if (! in_array('auth', $middleware, true)) {
                    $this->errors[] = "Route Admin thiếu auth: {$name} [{$uri}]";
                }

                if (! in_array('admin.access', $middleware, true)) {
                    $this->errors[] = "Route Admin thiếu admin.access: {$name} [{$uri}]";
                }
            }

            $isMutation = array_intersect($methods, ['POST', 'PUT', 'PATCH', 'DELETE']) !== [];
            $isPublicMutation = $isMutation
                && ! in_array('auth', $middleware, true)
                && ! str_starts_with($uri, '_');

            if ($isPublicMutation && ! $this->hasThrottle($middleware)) {
                $this->warnings[] = "Route public thay đổi dữ liệu chưa có throttle: {$name} [{$uri}]";
            }
        }

        $this->line('✓ Đã rà auth, admin.access và throttle trên toàn bộ route.');
    }

    /** @param list<string> $middleware */
    private function hasThrottle(array $middleware): bool
    {
        foreach ($middleware as $item) {
            if ($item === 'throttle' || str_starts_with($item, 'throttle:')) {
                return true;
            }
        }

        return false;
    }

    private function scanControllers(): void
    {
        $controllerPath = app_path('Http/Controllers');
        $patterns = [
            '/::all\s*\(/' => 'dùng ::all(), nên cân nhắc paginate/select',
            '/->get\s*\(\s*\)\s*;/' => 'dùng get() không giới hạn, cần rà với bảng lớn',
            '/request\(\)->all\s*\(/' => 'dùng request()->all(), nên dùng validated()/only()',
            '/\$request->all\s*\(/' => 'dùng $request->all(), nên dùng validated()/only()',
        ];

        foreach (File::allFiles($controllerPath) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $contents = File::get($file->getPathname());
            $relative = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file->getPathname());

            foreach ($patterns as $pattern => $message) {
                if (preg_match($pattern, $contents) === 1) {
                    $this->warnings[] = "{$relative}: {$message}.";
                }
            }
        }

        $this->line('✓ Đã quét controller tìm query tải toàn bộ và mass-assignment nguy hiểm.');
    }

    private function checkDatabaseIndexes(): void
    {
        $requirements = [
            'orders' => [
                ['user_id'],
                ['order_code'],
                ['order_status'],
                ['created_at'],
            ],
            'order_items' => [
                ['order_id'],
                ['product_id'],
                ['sku_id'],
            ],
            'payments' => [
                ['order_id'],
                ['status'],
            ],
            'shipments' => [
                ['order_id'],
                ['status'],
            ],
            'inventories' => [
                ['warehouse_id', 'sku_id'],
            ],
            'notifications' => [
                ['notifiable_type', 'notifiable_id'],
                ['read_at'],
            ],
            'product_statistics' => [
                ['product_id'],
            ],
            'search_histories' => [
                ['user_id'],
                ['created_at'],
            ],
            'login_histories' => [
                ['user_id'],
                ['logged_in_at'],
            ],
        ];

        foreach ($requirements as $table => $columnSets) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            try {
                $indexes = Schema::getIndexes($table);
            } catch (Throwable) {
                $this->warnings[] = "Không đọc được index của bảng {$table}.";
                continue;
            }

            foreach ($columnSets as $columns) {
                if (! $this->hasIndexForColumns($indexes, $columns)) {
                    $this->warnings[] = sprintf(
                        'Bảng %s chưa thấy index phù hợp cho (%s).',
                        $table,
                        implode(', ', $columns)
                    );
                }
            }
        }

        $this->line('✓ Đã đối chiếu index cho các bảng nghiệp vụ truy cập thường xuyên.');
    }

    /**
     * @param array<int, array<string, mixed>> $indexes
     * @param list<string> $requiredColumns
     */
    private function hasIndexForColumns(array $indexes, array $requiredColumns): bool
    {
        foreach ($indexes as $index) {
            $columns = array_values($index['columns'] ?? []);

            if (array_slice($columns, 0, count($requiredColumns)) === $requiredColumns) {
                return true;
            }
        }

        return false;
    }

    private function printResult(): void
    {
        if ($this->warnings !== []) {
            $this->newLine();
            $this->warn('CẢNH BÁO (' . count($this->warnings) . ')');
            foreach ($this->warnings as $index => $warning) {
                $this->line(($index + 1) . '. ' . $warning);
            }
        }

        if ($this->errors !== []) {
            $this->newLine();
            $this->error('LỖI (' . count($this->errors) . ')');
            foreach ($this->errors as $index => $error) {
                $this->line(($index + 1) . '. ' . $error);
            }
        }

        $this->newLine();

        if ($this->errors === [] && $this->warnings === []) {
            $this->info('Không phát hiện vấn đề bảo mật hoặc hiệu năng rõ ràng.');
            return;
        }

        if ($this->errors === []) {
            $this->warn('Không có lỗi nghiêm trọng, nhưng còn cảnh báo cần rà.');
            return;
        }

        $this->error('Kiểm tra thất bại. Hãy xử lý lỗi trước khi triển khai production.');
    }
}
