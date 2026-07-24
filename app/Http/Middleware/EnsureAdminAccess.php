<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminAccess
{
    /**
     * Kiểm tra người dùng có được phép truy cập khu vực quản trị hay không.
     */
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        /*
        |--------------------------------------------------------------------------
        | Kiểm tra đăng nhập
        |--------------------------------------------------------------------------
        |
        | Trường hợp middleware này bị sử dụng mà không đi kèm middleware auth,
        | người dùng chưa đăng nhập sẽ được chuyển về trang đăng nhập.
        |
        */

        if (! $request->user()) {
            return redirect()
                ->route('login')
                ->with(
                    'error',
                    'Vui lòng đăng nhập để truy cập trang quản trị.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Các role được phép truy cập Admin
        |--------------------------------------------------------------------------
        |
        | super_admin:
        | Có toàn quyền quản trị hệ thống.
        |
        | admin:
        | Quản trị viên thông thường.
        |
        | staff:
        | Nhân viên xử lý sản phẩm, đơn hàng và nghiệp vụ chung.
        |
        | warehouse_staff:
        | Nhân viên kho.
        |
        | customer_support:
        | Nhân viên chăm sóc khách hàng.
        |
        */

        $allowedRoles = [
            'super_admin',
            'admin',
            'staff',
            'warehouse_staff',
            'customer_support',
        ];

        if (! $request->user()->hasAnyRole($allowedRoles)) {
            abort(
                Response::HTTP_FORBIDDEN,
                'Bạn không có quyền truy cập khu vực quản trị.'
            );
        }

        return $next($request);
    }
}
