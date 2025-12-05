<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'is_active',
        'parent_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        if (!$this->image) {
            return null;
        }
        if (filter_var($this->image, FILTER_VALIDATE_URL)) {
            return $this->image;
        }
        return asset('storage/' . $this->image);
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'category', 'name');
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')->where('is_active', true)->orderBy('name', 'asc')->with('children');
    }

    public function isParent()
    {
        return $this->children()->count() > 0;
    }

    public function getFullPathAttribute()
    {
        $path = [$this->name];
        $parent = $this->parent;

        while ($parent) {
            array_unshift($path, $parent->name);
            $parent = $parent->parent;
        }

        return implode(' → ', $path);
    }

    /**
     * Get all descendant category names recursively (including self)
     */
    public function getAllDescendantNames()
    {
        $names = [$this->name];
        
        $children = $this->children()->get();
        foreach ($children as $child) {
            $names = array_merge($names, $child->getAllDescendantNames());
        }
        
        return $names;
    }

    /**
     * Static method to get all category names by slug or name
     */
    public static function getCategoryNamesWithChildren($identifier)
    {
        // Try to find by slug first, then by name
        $category = self::where('slug', $identifier)
            ->orWhere('name', $identifier)
            ->first();
        
        if (!$category) {
            return [$identifier]; // Return the identifier itself if category not found
        }
        
        return $category->getAllDescendantNames();
    }
}
