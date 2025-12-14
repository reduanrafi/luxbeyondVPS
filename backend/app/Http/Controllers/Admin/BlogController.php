<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = BlogPost::with('author');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
        }

        if ($request->has('status')) {
            $query->where('is_published', $request->status === 'published');
        }

        return response()->json($query->latest()->paginate(10));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'tags' => 'nullable|array',
            'is_published' => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('blog-images', 'public');
            $validated['image'] = $path;
        }

        $validated['author_id'] = auth()->id();
        if ($validated['is_published'] ?? false) {
            $validated['published_at'] = now();
        }

        $post = BlogPost::create($validated);

        return response()->json($post, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $post = BlogPost::with('author')->findOrFail($id);
        return response()->json($post);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $post = BlogPost::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'excerpt' => 'nullable|string',
            'image' => 'nullable', // Can be file or string (if keeping existing)
            'tags' => 'nullable|array',
            'is_published' => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            // Delete old image
            if ($post->image) {
                Storage::disk('public')->delete($post->image);
            }
            $path = $request->file('image')->store('blog-images', 'public');
            $validated['image'] = $path;
        }

        if (isset($validated['is_published'])) {
            if ($validated['is_published'] && !$post->is_published) {
                $validated['published_at'] = now();
            }
        }
        
        // Handle slug update if title changes
        if (isset($validated['title']) && $validated['title'] !== $post->title) {
            $validated['slug'] = Str::slug($validated['title']) . '-' . Str::random(6);
        }

        $post->update($validated);

        return response()->json($post);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $post = BlogPost::findOrFail($id);
        if ($post->image) {
            Storage::disk('public')->delete($post->image);
        }
        $post->delete();
        return response()->json(['message' => 'Post deleted successfully']);
    }
}
