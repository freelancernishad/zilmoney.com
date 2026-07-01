<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

$user = User::where('email', 'freelancernishad123@gmail.com')->first();
if (!$user) {
    echo "User not found\n";
    exit;
}
auth()->login($user);

// Log all queries executed
DB::listen(function($query) {
    echo "SQL: " . $query->sql . "\n";
    echo "Bindings: " . json_encode($query->bindings) . "\n";
});

$request = Request::create('/zilmoney/payments', 'GET', [
    'page' => 1,
    'per_page' => 50,
    'sort_by' => 'created_at',
    'sort_order' => 'desc',
]);

$controller = app(\App\Http\Controllers\Zilmoney\PaymentController::class);
$response = $controller->index($request);

echo "Total records in response: " . count(json_decode($response->getContent(), true)['data']) . "\n";
