<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoginHistory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoginHistoryController extends Controller
{
    public function index(Request $request): View
    {
        $query = LoginHistory::query()->with('user:id,name,email');

        $keyword = trim((string) $request->input('keyword'));
        if ($keyword !== '') {
            $query->where(function ($builder) use ($keyword): void {
                $builder
                    ->where('email', 'like', "%{$keyword}%")
                    ->orWhere('ip_address', 'like', "%{$keyword}%")
                    ->orWhere('device', 'like', "%{$keyword}%")
                    ->orWhere('browser', 'like', "%{$keyword}%")
                    ->orWhere('platform', 'like', "%{$keyword}%")
                    ->orWhereHas('user', function ($userQuery) use ($keyword): void {
                        $userQuery
                            ->where('name', 'like', "%{$keyword}%")
                            ->orWhere('email', 'like', "%{$keyword}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('is_success', $request->input('status') === 'success');
        }

        if ($request->filled('date_from')) {
            $query->whereDate('logged_in_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('logged_in_at', '<=', $request->input('date_to'));
        }

        $histories = $query
            ->latestLogin()
            ->paginate(30)
            ->withQueryString();

        $statistics = LoginHistory::query()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN is_success = 1 THEN 1 ELSE 0 END) as success_count')
            ->selectRaw('SUM(CASE WHEN is_success = 0 THEN 1 ELSE 0 END) as failed_count')
            ->selectRaw('COUNT(DISTINCT ip_address) as unique_ips')
            ->first();

        return view('admin.login-histories.index', compact('histories', 'statistics'));
    }

    public function show(LoginHistory $loginHistory): View
    {
        $loginHistory->load('user:id,name,email');

        return view('admin.login-histories.show', compact('loginHistory'));
    }
}
