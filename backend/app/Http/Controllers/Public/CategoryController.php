<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('children')
        ->where('parent_id', null)
        ->where('is_active', true)->get ();

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }
}
