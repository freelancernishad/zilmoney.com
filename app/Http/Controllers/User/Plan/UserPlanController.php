<?php

namespace App\Http\Controllers\User\Plan;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


class UserPlanController extends Controller
{

    public function getActivePlan(Request $request)
    {
        $user = $request->user();

        $active = $user->planSubscriptions()
            ->where('status', 'active')
            ->with('plan') // Eager load plan
            ->latest('start_date')
            ->first();

        if (!$active) {
            $payAsYouGoPlan = \App\Models\Plan\Plan::where('name', 'like', '%Pay As You Go%')->first()
                ?? \App\Models\Plan\Plan::find(1);

            $activeData = [
                'id' => null,
                'user_id' => $user->id,
                'plan_id' => $payAsYouGoPlan ? $payAsYouGoPlan->id : 1,
                'status' => 'active',
                'is_default' => true,
                'plan' => $payAsYouGoPlan,
            ];
        } else {
            $activeData = $active->toArray();
        }

        $totalRechargeCredit = (float) $user->planSubscriptions()->where('status', 'active')->sum('final_amount');
        if ($totalRechargeCredit == 0) {
            $totalRechargeCredit = (float) $user->payments()->where('status', 'paid')->sum('amount');
        }

        $activeData['total_recharge_credit'] = $totalRechargeCredit;

        return response()->json($activeData);
    }

    public function getSubscriptionHistory(Request $request)
    {
        $user = $request->user();

        $subscriptions = $user->planSubscriptions()
            ->with('plan') // Eager load plan
            ->latest('start_date')
            ->paginate(10);

        return response()->json($subscriptions);
    }

    public function getUserPayments(Request $request)
    {
        $user = $request->user();

        $payments = $user->payments()
            ->latest('created_at')
            ->paginate(10);

        return response()->json($payments);
    }


}
