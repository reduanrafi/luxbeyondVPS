<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $query = BlogPost::where('is_published', true)->with('author:id,name');

        if ($request->has('tag')) {
            $query->whereJsonContains('tags', $request->tag);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        return response()->json($query->latest('published_at')->paginate(9));
    }

    public function show($slug)
    {
        $post = BlogPost::where('slug', $slug)
            ->where('is_published', true)
            ->with('author:id,name')
            ->firstOrFail();

        // Increment views
        $post->increment('views');

        return response()->json($post);
    }
    
    public function getRecent()
    {
        $posts = BlogPost::where('is_published', true)
            ->with('author:id,name')
            ->latest('published_at')
            ->take(3)
            ->get();
            
        return response()->json($posts);
    }
}
