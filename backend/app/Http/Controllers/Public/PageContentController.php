<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\PageContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PageContentController extends Controller
{
    public function index()
    {
        $pages = Cache::remember('pages_index', 300, function () {
            return PageContent::where('is_active', true)
                ->select(['id', 'key', 'title', 'section', 'is_active', 'updated_at'])
                ->orderBy('section')
                ->orderBy('title')
                ->get();
        });

        return response()->json($pages);
    }

    public function show(string $key)
    {
        \Log::info($key);
        $page = Cache::remember("page_{$key}", 300, function () use ($key) {
            return PageContent::where('key', $key)
                ->where('is_active', true)
                ->first();
        });


        if (!$page) {
            return response()->json(['message' => 'Page not found'], 404);
        }

        return response()->json($page);
    }
}
