<?php

namespace App\Http\Controllers\Admin\Plans;


use App\Models\Plan\Plan;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Admin\Plans\AdminPlanStoreRequest;

class PlanController extends Controller
{
    // Fetch all plans (list of plans)
    public function index(Request $request)
    {
        $query = Plan::query();

        // Check if request is from admin route
        $isAdminRoute = $request->is('api/admin/*') || $request->is('admin/*');

        if (!$isAdminRoute && !$request->has('all')) {
            // Public / User requests only see active plans in DB
            $query->where('is_active', true);
        } elseif ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Order by serial ascending, then id ascending
        $plans = $query->orderBy('serial', 'asc')->orderBy('id', 'asc')->get(); 
        
        $user = auth('user')->user();
        
        // If no user found via standard auth, try to parse token explicitly (since route is public)
        $token = request()->bearerToken();
        \Illuminate\Support\Facades\Log::info("Plan list check - Bearer Token: " . ($token ? substr($token, 0, 10) . '...' : 'None'));

        if (!$user && $token) {
            try {
                // Explicitly set the token
                \Tymon\JWTAuth\Facades\JWTAuth::setToken($token);
                if ($payload = \Tymon\JWTAuth\Facades\JWTAuth::getPayload()) {
                    $user = \App\Models\User::find($payload->get('sub'));
                    \Illuminate\Support\Facades\Log::info("Plan list check - User found via token: " . ($user ? $user->id : 'Null'));
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Plan list check - Token parsing failed: " . $e->getMessage());
            }
        }
        
        $activePlanId = null;
        if ($user) {
            $activeSub = $user->planSubscriptions()
                ->where('status', 'active')
                ->latest('start_date')
                ->first();
            $activePlanId = $activeSub ? $activeSub->plan_id : null;
            
            \Illuminate\Support\Facades\Log::info("Plan list check - User: {$user->id}, Active Plan ID: " . ($activePlanId ?? 'None'));
        } else {
            \Illuminate\Support\Facades\Log::info("Plan list check - No user found");
        }

        // Transform to array to ensure DB properties and user status are included in JSON
        $plansData = $plans->map(function ($plan) use ($activePlanId, $user) {
            $data = $plan->toArray();
            $data['is_active'] = (bool) $plan->is_active;
            $data['serial'] = (int) $plan->serial;
            $data['is_current_plan'] = (bool) ($activePlanId && $activePlanId == $plan->id);
            
            // Pay-As-You-Go credit recharge mode
            $data['proration_credit'] = 0;
            $data['pay_today'] = $plan->discounted_price;
            $data['is_downgrade_blocked'] = false;

            return $data;
        });

        return response()->json([
            'plans' => $plansData
        ]);
    }

    // Fetch a single plan by ID
    public function show($id)
    {
        $plan = Plan::find($id); // Find plan by ID

        if (!$plan) {
            return response()->json(['message' => 'Plan not found'], 404);
        }

        return response()->json($plan->makeVisible('features'));
    }

    // Create a new plan
    public function store(AdminPlanStoreRequest $request)
    {
        $plan = Plan::create([
            'name' => $request->name,
            'duration' => $request->duration,
            'original_price' => $request->original_price,
            'monthly_price' => $request->monthly_price,
            'discount_percentage' => $request->discount_percentage,
            'features' => $request->features, // stored as JSON array
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : true,
            'serial' => $request->input('serial', 0),
        ]);

        return response()->json([
            'message' => 'Plan created successfully',
            'plan' => $plan->makeVisible('features'),
        ], 201);
    }

    public function update(AdminPlanStoreRequest $request, $id)
    {
        $plan = Plan::findOrFail($id);

        $updateData = [
            'name' => $request->name,
            'duration' => $request->duration,
            'original_price' => $request->original_price,
            'monthly_price' => $request->monthly_price,
            'discount_percentage' => $request->discount_percentage,
            'features' => $request->features,
        ];

        if ($request->has('is_active')) {
            $updateData['is_active'] = $request->boolean('is_active');
        }

        if ($request->has('serial')) {
            $updateData['serial'] = (int) $request->input('serial', 0);
        }

        $plan->update($updateData);

        return response()->json([
            'message' => 'Plan updated successfully',
            'plan' => $plan->makeVisible('features'),
        ]);
    }


    // Delete a plan
    public function destroy($id)
    {
        $plan = Plan::find($id); // Find plan by ID

        if (!$plan) {
            return response()->json(['message' => 'Plan not found'], 404);
        }

        $plan->delete(); // Delete the plan
        return response()->json(['message' => 'Plan deleted successfully']);
    }
}
