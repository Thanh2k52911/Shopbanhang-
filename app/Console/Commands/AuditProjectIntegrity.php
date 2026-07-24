<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route as RouteFacade;
use ReflectionClass;
use Throwable;

class AuditProjectIntegrity extends Command
{
    protected $signature = 'project:audit
        {--lint : Chạy php -l cho toàn bộ file PHP trong app, routes, database và bootstrap}
        {--strict : Coi cảnh báo link # là lỗi}';

    protected $description = 'Rà soát route, controller method, view, file module và các service method quan trọng của CosmeticShop';

    /** @var array<int, array{type:string,message:string}> */
    private array $issues = [];

    public function handle(): int
    {
        $this->newLine();
        $this->components->info('COSMETICSHOP - KIỂM TRA TÍNH TOÀN VẸN DỰ ÁN');

        $this->checkCriticalFiles();
        $this->checkCriticalMethods();
        $this->checkRoutes();
        $this->checkStaticViews();
        $this->checkDeadClientLinks();

        if ((bool) $this->option('lint')) {
            $this->lintPhpFiles();
        }

        return $this->printSummary();
    }

    private function checkCriticalFiles(): void
    {
        $files = [
            'app/Services/OrderService.php',
            'app/Services/OrderCancellationService.php',
            'app/Services/LoyaltyService.php',
            'app/Services/Admin/NotificationService.php',
            'app/Services/Admin/ShipmentStatusService.php',
            'app/Services/Admin/ReturnRequestStatusService.php',
            'app/Services/Admin/RefundService.php',
            'app/Services/Admin/ReturnInventoryService.php',
            'app/Http/Middleware/AuditAdminRequest.php',
            'resources/views/admin/roles/edit.blade.php',
            'resources/views/admin/pages/index.blade.php',
            'resources/views/admin/shipping-methods/index.blade.php',
            'resources/views/admin/variant-attributes/index.blade.php',
            'resources/views/client/contact/create.blade.php',
            'resources/views/client/account/addresses/index.blade.php',
        ];

        foreach ($files as $file) {
            if (! File::exists(base_path($file))) {
                $this->errorIssue("Thiếu file bắt buộc: {$file}");
            }
        }

        $this->line('✓ Đã kiểm tra danh sách file module quan trọng.');
    }

    private function checkCriticalMethods(): void
    {
        $contracts = [
            \App\Services\OrderCancellationService::class => [
                'cancelByCustomer',
                'cancelByAdmin',
            ],
            \App\Services\LoyaltyService::class => [
                'awardCompletedOrder',
            ],
            \App\Services\Admin\NotificationService::class => [
                'notifyNewOrder',
                'notifyNewQuestion',
                'notifyNewReview',
                'notifyNewReturnRequest',
                'notifyNewContact',
                'notifyLowStock',
            ],
        ];

        foreach ($contracts as $class => $methods) {
            if (! class_exists($class)) {
                $this->errorIssue("Không tìm thấy class: {$class}");
                continue;
            }

            foreach ($methods as $method) {
                if (! method_exists($class, $method)) {
                    $this->errorIssue("Thiếu method {$class}::{$method}()");
                }
            }
        }

        $this->line('✓ Đã kiểm tra các method hợp đồng giữa controller và service.');
    }

    private function checkRoutes(): void
    {
        $names = [];

        /** @var Route $route */
        foreach (RouteFacade::getRoutes() as $route) {
            $name = $route->getName();

            if ($name !== null) {
                if (isset($names[$name])) {
                    $this->errorIssue("Trùng tên route: {$name}");
                }
                $names[$name] = true;
            }

            $action = $route->getActionName();

            if ($action === 'Closure' || ! str_contains($action, '@')) {
                continue;
            }

            [$controller, $method] = explode('@', $action, 2);

            if (! class_exists($controller)) {
                $this->errorIssue("Route {$this->routeLabel($route)} trỏ tới controller không tồn tại: {$controller}");
                continue;
            }

            if (! method_exists($controller, $method)) {
                $this->errorIssue("Route {$this->routeLabel($route)} trỏ tới method không tồn tại: {$controller}::{$method}()");
            }
        }

        $this->line('✓ Đã kiểm tra controller và method của toàn bộ route.');
    }

    private function checkStaticViews(): void
    {
        $controllerFiles = File::allFiles(app_path('Http/Controllers'));
        $checked = 0;

        foreach ($controllerFiles as $file) {
            $content = File::get($file->getPathname());

            preg_match_all(
                '/(?:view|View::make)\(\s*[\'\"]([^\'\"]+)[\'\"]/',
                $content,
                $matches
            );

            foreach (array_unique($matches[1] ?? []) as $viewName) {
                if (str_contains($viewName, '$') || str_contains($viewName, '{')) {
                    continue;
                }

                $checked++;
                $path = resource_path('views/' . str_replace('.', '/', $viewName) . '.blade.php');

                if (! File::exists($path)) {
                    $relativeController = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file->getPathname());
                    $this->errorIssue("Thiếu view [{$viewName}] được gọi tại {$relativeController}");
                }
            }
        }

        $this->line("✓ Đã kiểm tra {$checked} lời gọi view tĩnh trong controller.");
    }

    private function checkDeadClientLinks(): void
    {
        $viewRoot = resource_path('views/client');

        if (! File::isDirectory($viewRoot)) {
            $this->warningIssue('Không tìm thấy thư mục resources/views/client.');
            return;
        }

        $count = 0;
        $examples = [];

        foreach (File::allFiles($viewRoot) as $file) {
            $content = File::get($file->getPathname());
            $matches = preg_match_all('/href\s*=\s*[\'\"]#[\'\"]/i', $content);

            if ($matches > 0) {
                $count += $matches;
                if (count($examples) < 10) {
                    $examples[] = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file->getPathname());
                }
            }
        }

        if ($count > 0) {
            $message = "Còn {$count} link href=\"#\" trong giao diện client";
            $message .= $examples !== [] ? ': ' . implode(', ', $examples) : '';

            if ((bool) $this->option('strict')) {
                $this->errorIssue($message);
            } else {
                $this->warningIssue($message);
            }
        } else {
            $this->line('✓ Không còn link href="#" trong giao diện client.');
        }
    }

    private function lintPhpFiles(): void
    {
        $directories = [
            app_path(),
            base_path('routes'),
            base_path('database'),
            base_path('bootstrap'),
        ];

        $files = [];

        foreach ($directories as $directory) {
            if (File::isDirectory($directory)) {
                foreach (File::allFiles($directory) as $file) {
                    if ($file->getExtension() === 'php') {
                        $files[] = $file->getPathname();
                    }
                }
            }
        }

        $bar = $this->output->createProgressBar(count($files));
        $bar->start();

        foreach ($files as $file) {
            $command = escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($file) . ' 2>&1';
            exec($command, $output, $exitCode);

            if ($exitCode !== 0) {
                $relative = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file);
                $this->errorIssue("PHP lint lỗi tại {$relative}: " . implode(' ', $output));
            }

            $output = [];
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->line('✓ Đã chạy PHP lint toàn bộ source chính.');
    }

    private function routeLabel(Route $route): string
    {
        return sprintf(
            '[%s] %s (%s)',
            $route->getName() ?? 'không tên',
            $route->uri(),
            implode('|', $route->methods())
        );
    }

    private function errorIssue(string $message): void
    {
        $this->issues[] = ['type' => 'error', 'message' => $message];
    }

    private function warningIssue(string $message): void
    {
        $this->issues[] = ['type' => 'warning', 'message' => $message];
    }

    private function printSummary(): int
    {
        $errors = array_values(array_filter(
            $this->issues,
            fn (array $issue): bool => $issue['type'] === 'error'
        ));

        $warnings = array_values(array_filter(
            $this->issues,
            fn (array $issue): bool => $issue['type'] === 'warning'
        ));

        $this->newLine();

        if ($errors !== []) {
            $this->components->error('PHÁT HIỆN LỖI');
            foreach ($errors as $index => $issue) {
                $this->line(($index + 1) . '. ' . $issue['message']);
            }
        }

        if ($warnings !== []) {
            $this->newLine();
            $this->components->warn('CẢNH BÁO');
            foreach ($warnings as $index => $issue) {
                $this->line(($index + 1) . '. ' . $issue['message']);
            }
        }

        $this->newLine();

        if ($errors === []) {
            $this->components->info(
                'Không phát hiện lỗi tích hợp nghiêm trọng. Cảnh báo: ' . count($warnings)
            );

            return self::SUCCESS;
        }

        $this->components->error(
            'Tổng lỗi: ' . count($errors) . ' | Cảnh báo: ' . count($warnings)
        );

        return self::FAILURE;
    }
}
