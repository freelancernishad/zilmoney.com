<?php

namespace App\Http\Controllers\Zilmoney\Shop;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Tax\Registration;
use Exception;

class AdminStripeTaxRegistrationController extends Controller
{
    private function initStripe()
    {
        $secretKey = class_exists(SystemSetting::class) 
            ? SystemSetting::getValue('STRIPE_SECRET', config('services.stripe.secret'))
            : config('services.stripe.secret');

        if (!$secretKey) {
            $secretKey = env('STRIPE_SECRET_KEY', env('STRIPE_SECRET'));
        }

        if (!$secretKey) {
            throw new Exception('Stripe API secret key is not configured.');
        }

        Stripe::setApiKey($secretKey);
    }

    /**
     * List all current Stripe Tax registrations.
     */
    public function index()
    {
        try {
            $this->initStripe();
            $registrations = Registration::all(['limit' => 100]);
            return response()->json([
                'status' => 'success',
                'data' => $registrations->data,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Add a single state registration to Stripe Tax.
     */
    public function store(Request $request)
    {
        $request->validate([
            'state' => 'required|string|size:2',
            'country' => 'nullable|string',
        ]);

        $stateCode = strtoupper(trim($request->state));
        $countryCode = strtoupper(trim($request->country ?: 'US'));

        try {
            $this->initStripe();
            $registration = Registration::create([
                'country' => $countryCode,
                'country_options' => [
                    'us' => [
                        'state' => $stateCode,
                        'type' => 'state_sales_tax',
                    ],
                ],
                'active_from' => 'now',
            ]);

            return response()->json([
                'status' => 'success',
                'message' => "Successfully registered {$stateCode} state tax in Stripe.",
                'data' => $registration,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Bulk add state registrations to Stripe Tax.
     */
    public function bulkAdd(Request $request)
    {
        $states = $request->input('states', []);
        
        // If empty or 'ALL', default to all major US states
        if (empty($states) || in_array('ALL', $states)) {
            $states = [
                'AL', 'AK', 'AZ', 'AR', 'CA', 'CO', 'CT', 'DE', 'FL', 'GA',
                'HI', 'ID', 'IL', 'IN', 'IA', 'KS', 'KY', 'LA', 'ME', 'MD',
                'MA', 'MI', 'MN', 'MS', 'MO', 'MT', 'NE', 'NV', 'NH', 'NJ',
                'NM', 'NY', 'NC', 'ND', 'OH', 'OK', 'OR', 'PA', 'RI', 'SC',
                'SD', 'TN', 'TX', 'UT', 'VT', 'VA', 'WA', 'WV', 'WI', 'WY', 'DC'
            ];
        }

        try {
            $this->initStripe();
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }

        $successful = [];
        $errors = [];

        foreach ($states as $st) {
            $stateCode = strtoupper(trim($st));
            try {
                $registration = Registration::create([
                    'country' => 'US',
                    'country_options' => [
                        'us' => [
                            'state' => $stateCode,
                            'type' => 'state_sales_tax',
                        ],
                    ],
                    'active_from' => 'now',
                ]);
                $successful[] = $stateCode;
            } catch (Exception $e) {
                $errors[] = [
                    'state' => $stateCode,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => "Bulk registration completed. Successfully registered " . count($successful) . " state(s).",
            'successful' => $successful,
            'errors' => $errors,
        ]);
    }
}
