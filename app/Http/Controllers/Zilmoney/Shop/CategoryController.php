<?php

namespace App\Http\Controllers\Zilmoney\Shop;

use App\Http\Controllers\Controller;
use App\Models\Shop\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories.
     */
    public function index()
    {
        $categories = Category::withCount('products')->get();
        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image_url' => 'nullable|string',
            'badge_text' => 'nullable|string',
            'feature_items' => 'nullable',
            'cta_button_text' => 'nullable|string',
        ]);

        $featureItems = $request->feature_items;
        if (is_string($featureItems)) {
            $featureItems = array_values(array_filter(array_map('trim', explode("\n", str_replace("\r", "", $featureItems)))));
        }

        $category = Category::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'image_url' => $request->image_url,
            'badge_text' => $request->badge_text,
            'feature_items' => $featureItems,
            'cta_button_text' => $request->cta_button_text,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully',
            'data' => $category,
        ], 201);
    }

    /**
     * Update the specified category.
     */
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'image_url' => 'nullable|string',
            'badge_text' => 'nullable|string',
            'feature_items' => 'nullable',
            'cta_button_text' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($request->has('name')) {
            $category->name = $request->name;
            $category->slug = Str::slug($request->name);
        }
        if ($request->has('description')) {
            $category->description = $request->description;
        }
        if ($request->has('image_url')) {
            $category->image_url = $request->image_url;
        }
        if ($request->has('badge_text')) {
            $category->badge_text = $request->badge_text;
        }
        if ($request->has('feature_items')) {
            $featureItems = $request->feature_items;
            if (is_string($featureItems)) {
                $featureItems = array_values(array_filter(array_map('trim', explode("\n", str_replace("\r", "", $featureItems)))));
            }
            $category->feature_items = $featureItems;
        }
        if ($request->has('cta_button_text')) {
            $category->cta_button_text = $request->cta_button_text;
        }
        if ($request->has('is_active')) {
            $category->is_active = $request->is_active;
        }

        $category->save();

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully',
            'data' => $category,
        ]);
    }

    /**
     * Remove the specified category.
     */
    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully',
        ]);
    }
}
