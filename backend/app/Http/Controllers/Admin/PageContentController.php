<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PageContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class PageContentController extends Controller
{
    public function index(Request $request)
    {
        $query = PageContent::query();

        if ($request->filled('search')) {
            $query->where('title', 'like', "%{$request->search}%")
                  ->orWhere('key', 'like', "%{$request->search}%");
        }

        if ($request->filled('section')) {
            $query->where('section', $request->section);
        }

        return response()->json($query->orderBy('section')->orderBy('title')->get());
    }

    public function show(string $id)
    {
        $page = PageContent::findOrFail($id);
        return response()->json($page);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'key'     => 'required|string|unique:page_contents,key|max:100',
            'title'   => 'required|string|max:255',
            'section' => 'required|in:page,hero,home',
        ]);

        $content = $this->buildContent($request);
        $page = PageContent::create([
            ...$validated,
            'content'   => $content,
            'is_active' => $request->boolean('is_active', true),
        ]);
        
        Cache::forget('pages_index');

        return response()->json($page, 201);
    }

    public function update(Request $request, string $id)
    {
        $page = PageContent::findOrFail($id);

        $validated = $request->validate([
            'title'     => 'sometimes|string|max:255',
            'is_active' => 'sometimes|boolean',
        ]);

        $content = $this->buildContent($request, $page->content ?? []);

        $page->update([
            ...$validated,
            'content' => $content,
        ]);

        Cache::forget('pages_index');
        Cache::forget("page_{$page->key}");

        return response()->json($page->fresh());
    }

    public function destroy(string $id)
    {
        $page = PageContent::findOrFail($id);

        // Clean up any stored images
        $content = $page->content ?? [];
        foreach (['desktop_image', 'mobile_image', 'image'] as $field) {
            if (!empty($content[$field]) && !str_starts_with($content[$field], 'http')) {
                Storage::disk('public')->delete($content[$field]);
            }
        }

        Cache::forget('pages_index');
        Cache::forget("page_{$page->key}");

        $page->delete();
        return response()->json(['message' => 'Page deleted successfully']);
    }

    /**
     * Build the content JSON from the request, merging with existing data.
     */
    private function buildContent(Request $request, array $existing = []): array
    {
        $content = $existing;

        // If the frontend sends a 'content' field as a JSON string, decode it
        if ($request->has('content') && is_string($request->input('content'))) {
            $decoded = json_decode($request->input('content'), true);
            if (is_array($decoded)) {
                $content = array_merge($content, $decoded);
            }
        }

        // Individual text fields (for backward compatibility)
        $textFields = ['heading', 'subheading', 'body', 'cta_text', 'cta_link', 'meta_title', 'meta_description', 'title', 'button_text', 'button_link'];
        foreach ($textFields as $field) {
            if ($request->has($field) && !is_null($request->input($field))) {
                $content[$field] = $request->input($field);
            }
        }

        // --- Handle File Uploads ---

        // Desktop image upload
        if ($request->hasFile('desktop_image')) {
            if (!empty($content['desktop_image']) && !str_starts_with($content['desktop_image'], 'http')) {
                Storage::disk('public')->delete($content['desktop_image']);
            }
            $path = $request->file('desktop_image')->store('page-images', 'public');
            $content['desktop_image'] = Storage::disk('public')->url($path);
        }

        // Mobile image upload
        if ($request->hasFile('mobile_image')) {
            if (!empty($content['mobile_image']) && !str_starts_with($content['mobile_image'], 'http')) {
                Storage::disk('public')->delete($content['mobile_image']);
            }
            $path = $request->file('mobile_image')->store('page-images', 'public');
            $content['mobile_image'] = Storage::disk('public')->url($path);
        }

        // Generic single image
        if ($request->hasFile('image')) {
            if (!empty($content['image']) && !str_starts_with($content['image'], 'http')) {
                Storage::disk('public')->delete($content['image']);
            }
            $path = $request->file('image')->store('page-images', 'public');
            $content['image'] = Storage::disk('public')->url($path);
        }

        // --- Handle Multi-Slide Banner Image Uploads ---
        // Expected keys: slide_0_desktop, slide_1_mobile, etc.
        if (isset($content['slides']) && is_array($content['slides'])) {
            foreach ($request->allFiles() as $key => $file) {
                if (preg_match('/^slide_(\d+)_(desktop|mobile)$/', $key, $matches)) {
                    $index = (int)$matches[1];
                    $type = $matches[2];

                    if (isset($content['slides'][$index])) {
                        // Delete old image if it exists
                        $oldPath = $content['slides'][$index][$type] ?? '';
                        if (!empty($oldPath) && !str_starts_with($oldPath, 'http')) {
                            // Convert URL back to path if necessary
                            $pathToRemove = str_replace(Storage::url(''), '', $oldPath);
                            Storage::disk('public')->delete($pathToRemove);
                        }

                        // Store new image
                        $path = $file->store('page-images', 'public');
                        $content['slides'][$index][$type] = Storage::disk('public')->url($path);
                    }
                }
            }
        }

        return $content;
    }
}
