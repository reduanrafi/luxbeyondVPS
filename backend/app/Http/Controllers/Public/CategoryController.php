<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Cache::remember('public_categories', 600, function () {
            return Category::with('children')
                ->where('parent_id', null)
                ->where('is_active', true)
                ->get();
        });

        return response()->json([
            'success' => true,
            'data' => $categories
        ])->header('Cache-Control', 'public, max-age=300, s-maxage=600');
    }
}
