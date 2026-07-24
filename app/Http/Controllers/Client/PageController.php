<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PageController extends Controller
{
    public function show(string $slug): View
    {
        $page = DB::table('pages')
            ->where('slug', $slug)
            ->where('status', true)
            ->whereNull('deleted_at')
            ->first();

        abort_if(!$page, 404);

        return view('client.page.show', compact('page'));
    }
}
