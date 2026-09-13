<?php

namespace App\Http\Controllers\Zilmoney\Shop;

use App\Http\Controllers\Controller;
use App\Models\Shop\Filter;
use App\Models\Shop\FilterValue;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FilterController extends Controller
{
    /**
     * Display listing of all relational filters with option values and counts.
     */
    public function index(Request $request)
    {
        $categorySlug = $request->query('category');

        $filters = Filter::with(['values' => function ($q) use ($categorySlug) {
            $q->withCount(['products' => function ($pq) use ($categorySlug) {
                if ($categorySlug && $categorySlug !== 'all') {
                    $pq->whereHas('category', function ($cq) use ($categorySlug) {
                        $cq->where('slug', $categorySlug);
                    });
                }
            }]);
        }])->get();

        return response()->json([
            'success' => true,
            'data' => $filters,
        ]);
    }

    /**
     * Store a new filter group label.
     */
    public function storeGroup(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $filter = Filter::firstOrCreate(
            ['slug' => Str::slug($request->name)],
            ['name' => $request->name, 'is_active' => true]
        );

        return response()->json([
            'success' => true,
            'message' => 'Filter Label Group created successfully',
            'data' => $filter->load('values'),
        ], 201);
    }

    /**
     * Store a new filter option value under a group.
     */
    public function store(Request $request)
    {
        $request->validate([
            'filter_name' => 'required|string|max:255',
            'value' => 'required|string|max:255',
        ]);

        $filter = Filter::firstOrCreate(
            ['slug' => Str::slug($request->filter_name)],
            ['name' => $request->filter_name, 'is_active' => true]
        );

        $filterValue = FilterValue::create([
            'filter_id' => $filter->id,
            'value' => $request->value,
            'slug' => Str::slug($request->value),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Filter option added successfully',
            'data' => $filterValue,
        ], 201);
    }

    /**
     * Delete a filter group label.
     */
    public function destroyGroup($id)
    {
        $filter = Filter::findOrFail($id);
        $filter->delete();

        return response()->json([
            'success' => true,
            'message' => 'Filter Label Group deleted successfully',
        ]);
    }

    /**
     * Delete a filter value option.
     */
    public function destroyValue($id)
    {
        $fv = FilterValue::findOrFail($id);
        $fv->delete();

        return response()->json([
            'success' => true,
            'message' => 'Filter option deleted successfully',
        ]);
    }
}
