<?php

use App\Models\User;
use Illuminate\Http\Request;

$user = User::where('email', 'freelancernishad123@gmail.com')->first();
if (!$user) {
    echo "User not found\n";
    exit;
}
auth()->login($user);

$request = Request::create('/zilmoney/payments', 'GET', [
    'per_page' => 50,
]);

$controller = app(\App\Http\Controllers\Zilmoney\PaymentController::class);
$response = $controller->index($request);

echo $response->getContent() . "\n";
