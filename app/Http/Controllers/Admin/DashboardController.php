<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductQuestion;
use App\Models\ProductReview;
use App\Models\ReturnRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    /**
     * Hiển thị trang tổng quan Admin.
     */
    public function index(): View
    {
        /*
        |--------------------------------------------------------------------------
        | Thống kê đơn hàng
        |--------------------------------------------------------------------------
        */

        $totalOrders = Order::query()->count();

        $pendingOrders = Order::query()
            ->pending()
            ->count();

        $completedOrders = Order::query()
            ->completed()
            ->count();

        $cancelledOrders = Order::query()
            ->cancelled()
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Doanh thu
        |--------------------------------------------------------------------------
        |
        | Chỉ tính các đơn đã hoàn thành.
        |
        */

        $totalRevenue = Order::query()
            ->completed()
            ->sum('total_amount');

        $todayRevenue = Order::query()
            ->completed()
            ->whereDate('completed_at', today())
            ->sum('total_amount');

        $monthRevenue = Order::query()
            ->completed()
            ->whereYear('completed_at', now()->year)
            ->whereMonth('completed_at', now()->month)
            ->sum('total_amount');

        /*
        |--------------------------------------------------------------------------
        | Thống kê khách hàng
        |--------------------------------------------------------------------------
        |
        | Chỉ đếm tài khoản có role customer.
        |
        */

        $totalCustomers = User::query()
            ->whereHas('roles', function ($query): void {
                $query->where('roles.name', 'customer');
            })
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Thống kê sản phẩm và tồn kho
        |--------------------------------------------------------------------------
        */

        $totalProducts = Product::query()->count();

        $activeProducts = Product::query()
            ->active()
            ->count();

        $lowStockItems = Inventory::query()
            ->lowStock()
            ->count();

        $outOfStockItems = Inventory::query()
            ->outOfStock()
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Các tác vụ đang chờ Admin xử lý
        |--------------------------------------------------------------------------
        */

        $pendingReviews = ProductReview::query()
            ->pending()
            ->count();

        $pendingQuestions = ProductQuestion::query()
            ->pending()
            ->count();

        $pendingReturnRequests = ReturnRequest::query()
            ->pending()
            ->count();

        $newContactMessages = ContactMessage::query()
            ->new()
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Danh sách đơn hàng mới nhất
        |--------------------------------------------------------------------------
        */

        $recentOrders = Order::query()
            ->with([
                'user:id,name,email',
            ])
            ->latest()
            ->limit(8)
            ->get([
                'id',
                'order_code',
                'user_id',
                'customer_name',
                'customer_email',
                'order_status',
                'payment_status',
                'shipping_status',
                'total_amount',
                'created_at',
            ]);

        return view('admin.dashboard.index', [
            'totalOrders' => $totalOrders,
            'pendingOrders' => $pendingOrders,
            'completedOrders' => $completedOrders,
            'cancelledOrders' => $cancelledOrders,

            'totalRevenue' => $totalRevenue,
            'todayRevenue' => $todayRevenue,
            'monthRevenue' => $monthRevenue,

            'totalCustomers' => $totalCustomers,

            'totalProducts' => $totalProducts,
            'activeProducts' => $activeProducts,
            'lowStockItems' => $lowStockItems,
            'outOfStockItems' => $outOfStockItems,

            'pendingReviews' => $pendingReviews,
            'pendingQuestions' => $pendingQuestions,
            'pendingReturnRequests' => $pendingReturnRequests,
            'newContactMessages' => $newContactMessages,

            'recentOrders' => $recentOrders,
        ]);
    }
}
