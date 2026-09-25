<?php

namespace App\Http\Controllers\Zilmoney\Shop;

use App\Http\Controllers\Controller;
use App\Models\Shop\Product;
use App\Models\Shop\Category;
use App\Services\FileSystem\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of shop products with dynamic relational filtering.
     */
    public function index(Request $request)
    {
        $query = Product::with(['category', 'filterValues.filter', 'colors', 'quantityTiers']);

        // 1. Category Filter (e.g. ?category=manual)
        if ($request->has('category') && $request->category !== 'all') {
            $categorySlug = $request->category;
            $query->whereHas('category', function ($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
            });
        }

        // 2. Relational Filter Values (Handles comma-separated string "Standard,Deskbook" or array)
        if ($request->has('filters') && !empty($request->input('filters'))) {
            $rawFilters = $request->input('filters');
            if (is_string($rawFilters)) {
                $filterValues = array_filter(explode(',', $rawFilters));
            } else {
                $filterValues = (array) $rawFilters;
            }

            foreach ($filterValues as $val) {
                if (empty($val)) continue;
                $slug = Str::slug($val);
                $query->whereHas('filterValues', function ($q) use ($val, $slug) {
                    $q->where('slug', $slug)->orWhere('value', $val);
                });
            }
        }

        // 3. Search Query (e.g. ?search=compact)
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('item_code', 'like', "%{$search}%")
                  ->orWhere('subtitle', 'like', "%{$search}%");
            });
        }

        // 4. Sorting
        $sortBy = $request->input('sort_by') ?? $request->input('sort') ?? $request->input('sortBy') ?? 'featured';
        if ($sortBy === 'price-asc') {
            $query->orderBy('starting_price', 'asc');
        } elseif ($sortBy === 'price-desc') {
            $query->orderBy('starting_price', 'desc');
        } elseif ($sortBy === 'code') {
            $query->orderBy('item_code', 'asc');
        } else {
            $query->orderBy('id', 'desc');
        }

        $perPage = (int) ($request->input('per_page') ?? $request->input('perPage') ?? 10);
        $products = $query->paginate($perPage);

        return response()->json($products);
    }

    /**
     * Display specified product details by ID or Slug.
     */
    public function show($idOrSlug)
    {
        $product = Product::with(['category', 'filterValues.filter', 'colors', 'quantityTiers'])
            ->where('id', $idOrSlug)
            ->orWhere('slug', $idOrSlug)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $product,
        ]);
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'item_code' => 'required|string|unique:shop_products,item_code',
            'category_id' => 'nullable|exists:shop_categories,id',
            'subtitle' => 'nullable|string',
            'image_url' => 'nullable|string',
            'images' => 'nullable|array',
            'images.*' => 'string',
            'description' => 'nullable|string',
            'filter_value_ids' => 'nullable|array',
            'filter_value_ids.*' => 'exists:shop_filter_values,id',
            'quantity_tiers' => 'nullable|array|min:1',
            'quantity_tiers.*.quantity' => 'required|integer|min:1',
            'quantity_tiers.*.price' => 'required|numeric|min:0',
        ]);

        $quantityTiers = $request->get('quantity_tiers', [
            ['quantity' => 250, 'price' => 95.99, 'is_popular' => true]
        ]);

        $images = $request->get('images', []);
        $primaryImageUrl = $request->image_url ?? ($images[0] ?? '/images/check-papers-preview.png');

        // Starting price & quantity from the first tier
        $startingQty = $quantityTiers[0]['quantity'] ?? 250;
        $startingPrice = $quantityTiers[0]['price'] ?? 95.99;

        $templateType = $request->get('template_type', 'business_deskbook_3up');
        $presets = Product::getDefaultPreviewPresets();
        $previewConfig = $request->get('preview_config') ?: ($presets[$templateType] ?? $presets['business_deskbook_3up']);

        $product = Product::create([
            'title' => $request->title,
            'slug' => Str::slug($request->title) . '-' . Str::random(4),
            'item_code' => $request->item_code,
            'subtitle' => $request->subtitle,
            'image_url' => $primaryImageUrl,
            'images' => $images,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'starting_quantity' => $startingQty,
            'starting_price' => $startingPrice,
            'in_stock' => $request->get('in_stock', true),
            'badge' => $request->badge,
            'template_type' => $templateType,
            'preview_config' => $previewConfig,
        ]);

        // Sync Checked Relational Filter Values
        if ($request->has('filter_value_ids')) {
            $product->filterValues()->sync($request->filter_value_ids);
        }

        // Sync Quantity Tiers
        foreach ($quantityTiers as $tier) {
            $qty = max(1, (int)$tier['quantity']);
            $price = (float)$tier['price'];
            $pricePerCheck = round($price / $qty, 4);

            $product->quantityTiers()->create([
                'quantity' => $qty,
                'price' => $price,
                'price_per_check' => $pricePerCheck,
                'is_popular' => !empty($tier['is_popular']),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully',
            'data' => $product->load(['category', 'filterValues', 'colors', 'quantityTiers']),
        ], 201);
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'item_code' => 'sometimes|required|string|unique:shop_products,item_code,' . $id,
            'category_id' => 'nullable|exists:shop_categories,id',
            'subtitle' => 'nullable|string',
            'image_url' => 'nullable|string',
            'images' => 'nullable|array',
            'images.*' => 'string',
            'description' => 'nullable|string',
            'template_type' => 'nullable|string',
            'preview_config' => 'nullable|array',
            'filter_value_ids' => 'nullable|array',
            'filter_value_ids.*' => 'exists:shop_filter_values,id',
            'quantity_tiers' => 'nullable|array|min:1',
            'quantity_tiers.*.quantity' => 'required|integer|min:1',
            'quantity_tiers.*.price' => 'required|numeric|min:0',
        ]);

        if ($request->has('title')) {
            $product->title = $request->title;
            $product->slug = Str::slug($request->title);
        }
        if ($request->has('item_code')) {
            $product->item_code = $request->item_code;
        }
        if ($request->has('subtitle')) {
            $product->subtitle = $request->subtitle;
        }
        if ($request->has('images')) {
            $product->images = $request->images;
            if (is_array($request->images) && count($request->images) > 0) {
                $product->image_url = $request->images[0];
            }
        }
        if ($request->has('image_url')) {
            $product->image_url = $request->image_url;
        }
        if ($request->has('category_id')) {
            $product->category_id = $request->category_id;
        }
        if ($request->has('description')) {
            $product->description = $request->description;
        }
        if ($request->has('template_type')) {
            $product->template_type = $request->template_type;
            if (!$request->has('preview_config') || empty($request->preview_config)) {
                $presets = Product::getDefaultPreviewPresets();
                $product->preview_config = $presets[$request->template_type] ?? $presets['business_deskbook_3up'];
            }
        }
        if ($request->has('preview_config') && !empty($request->preview_config)) {
            $product->preview_config = $request->preview_config;
        }

        // Sync Quantity Tiers if provided
        if ($request->has('quantity_tiers')) {
            $quantityTiers = $request->quantity_tiers;
            if (count($quantityTiers) > 0) {
                $product->starting_quantity = $quantityTiers[0]['quantity'];
                $product->starting_price = $quantityTiers[0]['price'];

                $product->quantityTiers()->delete();
                foreach ($quantityTiers as $tier) {
                  $qty = max(1, (int)$tier['quantity']);
                  $price = (float)$tier['price'];
                  $pricePerCheck = round($price / $qty, 4);

                  $product->quantityTiers()->create([
                      'quantity' => $qty,
                      'price' => $price,
                      'price_per_check' => $pricePerCheck,
                      'is_popular' => !empty($tier['is_popular']),
                  ]);
                }
            }
        }

        $product->save();

        // Sync Relational Filters
        if ($request->has('filter_value_ids')) {
            $product->filterValues()->sync($request->filter_value_ids);
        }

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'data' => $product->load(['category', 'filterValues', 'colors', 'quantityTiers']),
        ]);
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully',
        ]);
    }

    /**
     * Upload product image to AWS S3 using FileUploadService.
     */
    public function uploadImage(Request $request, FileUploadService $fileUploadService)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:10240',
        ]);

        try {
            $url = $fileUploadService->uploadFileToS3($request->file('image'), 'products');
            return response()->json([
                'success' => true,
                'url' => $url,
                'message' => 'Image uploaded to S3 successfully',
            ]);
        } catch (\Exception $e) {
            // Fallback to local public disk storage if S3 credentials are not set in environment
            $path = $request->file('image')->store('uploads/products', 'public');
            $url = asset('storage/' . $path);
            return response()->json([
                'success' => true,
                'url' => $url,
                'message' => 'Image uploaded (Local fallback): ' . $e->getMessage(),
            ]);
        }
    }
}
