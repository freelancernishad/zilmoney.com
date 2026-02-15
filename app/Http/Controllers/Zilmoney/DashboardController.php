<?php

namespace App\Http\Controllers\Zilmoney;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Aggregate data for dashboard
        $business = $user->businessDetails;
        $totalBalance = $business ? $business->accounts()->sum('balance') : 0; // Assuming 'balance' column exists next
        $recentTransactions = $business ? $business->payments()->latest()->take(5)->get() : [];
        $activePayees = $business ? $business->payees()->count() : 0;

        return response()->json([
            'overview' => [
                'total_balance' => $totalBalance,
                'active_payees_count' => $activePayees,
            ],
            'recent_transactions' => $recentTransactions,
            'business_profile' => $business,
        ]);
    }
}
