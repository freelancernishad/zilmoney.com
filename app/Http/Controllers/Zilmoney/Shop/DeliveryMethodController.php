<?php

namespace App\Http\Controllers\Zilmoney\Shop;

use App\Http\Controllers\Controller;
use App\Models\Shop\DeliveryMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DeliveryMethodController extends Controller
{
    /**
     * Display a listing of delivery methods.
     */
    public function index(Request $request)
    {
        // Auto seed default options if database table is empty
        if (DeliveryMethod::count() === 0) {
            DeliveryMethod::create([
                'code' => 'standard',
                'name' => 'Standard Ground Delivery',
                'speed' => '5-7 Business Days (Tracked UPS/FedEx)',
                'price' => 9.95,
                'icon' => 'Truck',
                'is_active' => true,
                'sort_order' => 1,
            ]);
            DeliveryMethod::create([
                'code' => 'priority',
                'name' => 'Priority Expedited',
                'speed' => '2-3 Business Days (Fast-Track Production)',
                'price' => 18.99,
                'icon' => 'Clock',
                'is_active' => true,
                'sort_order' => 2,
            ]);
            DeliveryMethod::create([
                'code' => 'overnight',
                'name' => 'Overnight Express Air',
                'speed' => 'Next Business Day Delivery',
                'price' => 34.99,
                'icon' => 'Zap',
                'is_active' => true,
                'sort_order' => 3,
            ]);
        }

        $query = DeliveryMethod::orderBy('sort_order', 'asc')->orderBy('id', 'asc');

        if (!$request->has('all')) {
            $query->where('is_active', true);
        }

        $methods = $query->get();

        return response()->json([
            'isError' => false,
            'success' => true,
            'data' => $methods,
        ]);
    }

    /**
     * Store a newly created delivery method.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'speed' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'icon' => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $code = Str::slug($validated['name']);
        // Check uniqueness of code
        if (DeliveryMethod::where('code', $code)->exists()) {
            $code = $code . '-' . time();
        }

        $method = DeliveryMethod::create([
            'code' => $code,
            'name' => $validated['name'],
            'speed' => $validated['speed'],
            'price' => $validated['price'],
            'icon' => $validated['icon'] ?? 'Truck',
            'is_active' => $request->has('is_active') ? (bool) $request->is_active : true,
            'sort_order' => $validated['sort_order'] ?? (DeliveryMethod::max('sort_order') + 1),
        ]);

        return response()->json([
            'isError' => false,
            'success' => true,
            'message' => 'Delivery method created successfully',
            'data' => $method,
        ], 201);
    }

    /**
     * Update the specified delivery method.
     */
    public function update(Request $request, $id)
    {
        $method = DeliveryMethod::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'speed' => 'sometimes|required|string|max:255',
            'price' => 'sometimes|required|numeric|min:0',
            'icon' => 'nullable|string|max:50',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        if (isset($validated['name'])) {
            $method->name = $validated['name'];
        }
        if (isset($validated['speed'])) {
            $method->speed = $validated['speed'];
        }
        if (isset($validated['price'])) {
            $method->price = $validated['price'];
        }
        if (array_key_exists('icon', $validated)) {
            $method->icon = $validated['icon'] ?? 'Truck';
        }
        if (isset($validated['is_active'])) {
            $method->is_active = (bool) $validated['is_active'];
        }
        if (isset($validated['sort_order'])) {
            $method->sort_order = $validated['sort_order'];
        }

        $method->save();

        return response()->json([
            'isError' => false,
            'success' => true,
            'message' => 'Delivery method updated successfully',
            'data' => $method,
        ]);
    }

    /**
     * Remove the specified delivery method.
     */
    public function destroy($id)
    {
        $method = DeliveryMethod::findOrFail($id);
        $method->delete();

        return response()->json([
            'isError' => false,
            'success' => true,
            'message' => 'Delivery method deleted successfully',
        ]);
    }
}
