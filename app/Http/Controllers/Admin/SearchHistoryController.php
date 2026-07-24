<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SearchHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SearchHistoryController extends Controller
{
    public function index(Request $request): View
    {
        $query = SearchHistory::query()
            ->with([
                'user:id,name,email',
                'clickedProduct:id,name,slug',
            ]);

        $keyword = trim((string) $request->input('keyword'));
        if ($keyword !== '') {
            $query->where('keyword', 'like', "%{$keyword}%");
        }

        if ($request->filled('result_status')) {
            $request->input('result_status') === 'empty'
                ? $query->withoutResults()
                : $query->withResults();
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $histories = $query
            ->latestSearches()
            ->paginate(30)
            ->withQueryString();

        $statistics = SearchHistory::query()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN result_count = 0 THEN 1 ELSE 0 END) as empty_count')
            ->selectRaw('SUM(CASE WHEN clicked_product_id IS NOT NULL THEN 1 ELSE 0 END) as clicked_count')
            ->selectRaw('COUNT(DISTINCT keyword) as unique_keywords')
            ->first();

        $topKeywords = SearchHistory::query()
            ->select('keyword')
            ->selectRaw('COUNT(*) as search_count')
            ->selectRaw('AVG(result_count) as average_results')
            ->selectRaw('SUM(CASE WHEN clicked_product_id IS NOT NULL THEN 1 ELSE 0 END) as click_count')
            ->groupBy('keyword')
            ->orderByDesc('search_count')
            ->limit(15)
            ->get();

        $emptyKeywords = SearchHistory::query()
            ->where('result_count', 0)
            ->select('keyword')
            ->selectRaw('COUNT(*) as search_count')
            ->groupBy('keyword')
            ->orderByDesc('search_count')
            ->limit(15)
            ->get();

        return view('admin.search-histories.index', compact(
            'histories',
            'statistics',
            'topKeywords',
            'emptyKeywords'
        ));
    }

    public function show(SearchHistory $searchHistory): View
    {
        $searchHistory->load([
            'user:id,name,email',
            'clickedProduct:id,name,slug',
        ]);

        return view('admin.search-histories.show', compact('searchHistory'));
    }
}
