<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::query();

        // Search
        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Status filter
        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Include relationships
        $query->with(['parent', 'children'])->withCount('products');

        // Check if requesting all categories (for dropdowns)
        if ($request->has('all') && $request->all) {
            return response()->json($query->orderBy('name')->get());
        }

        // Paginated response
        $categories = $query->orderBy('created_at', 'desc')->paginate($request->per_page ?? 15);
        return response()->json($categories);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories')->where(function ($query) use ($request) {
                    return $query->where('parent_id', $request->parent_id);
                })
            ],
            'description' => 'nullable|string',
            'image' => 'nullable|mimes:jpeg,png,jpg,gif,svg,webp,avif|max:2048',
            'is_active' => 'boolean',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        $validated['slug'] = \Str::slug($validated['name']);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('categories', 'public');
        }

        $category = Category::create($validated);
        return response()->json($category->load(['parent', 'children']), 201);
    }

    public function show($id)
    {
        $category = Category::withCount('products')->findOrFail($id);
        return response()->json($category);
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'name' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('categories')->where(function ($query) use ($request) {
                    return $query->where('parent_id', $request->parent_id);
                })->ignore($category->id)
            ],
            'description' => 'nullable|string',
            'image' => 'nullable|mimes:jpeg,png,jpg,gif,svg,webp,avif|max:2048',
            'is_active' => 'boolean',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        // Prevent circular reference
        if (isset($validated['parent_id']) && $validated['parent_id'] == $category->id) {
            return response()->json(['message' => 'A category cannot be its own parent'], 422);
        }

        // Prevent setting a descendant as parent
        if (isset($validated['parent_id'])) {
            $descendantIds = $this->getDescendantIds($category);
            if (in_array($validated['parent_id'], $descendantIds)) {
                return response()->json(['message' => 'Cannot set a descendant category as parent'], 422);
            }
        }

        if (isset($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        if ($request->hasFile('image')) {
            // Delete old image
            if ($category->image && Storage::disk('public')->exists($category->image)) {
                Storage::disk('public')->delete($category->image);
            }
            $validated['image'] = $request->file('image')->store('categories', 'public');
        }

        $category->update($validated);

        return response()->json([
            'message' => 'Category updated successfully',
            'category' => $category->load(['parent', 'children'])
        ]);
    }

    public function destroy(Category $category)
    {
        // Check if category has products
        if ($category->products()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete category with existing products'
            ], 422);
        }

        // Check if category has children
        if ($category->children()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete category with child categories'
            ], 422);
        }

        $category->delete();
        return response()->json(['message' => 'Category deleted successfully']);
    }

    /**
     * Get all descendant IDs of a category
     */
    private function getDescendantIds(Category $category)
    {
        $descendants = [];
        $children = $category->children;

        foreach ($children as $child) {
            $descendants[] = $child->id;
            $descendants = array_merge($descendants, $this->getDescendantIds($child));
        }

        return $descendants;
    }
}
