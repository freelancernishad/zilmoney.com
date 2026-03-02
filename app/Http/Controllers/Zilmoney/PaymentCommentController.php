<?php

namespace App\Http\Controllers\Zilmoney;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PaymentCommentController extends Controller
{
    public function index(Request $request, $paymentId)
    {
        $business = auth()->user()->businessDetails;
        if (!$business) return response()->json(['message' => 'Business profile required'], 400);

        $payment = $business->payments()->findOrFail($paymentId);

        return response()->json($payment->comments()->with('user')->get());
    }

    public function store(Request $request, $paymentId)
    {
        $business = auth()->user()->businessDetails;
        if (!$business) return response()->json(['message' => 'Business profile required'], 400);

        $payment = $business->payments()->findOrFail($paymentId);

        $validated = $request->validate([
            'comment' => 'required|string|max:1000'
        ]);

        $comment = $payment->comments()->create([
            'user_id' => auth()->id(),
            'comment' => $validated['comment']
        ]);

        return response()->json($comment->load('user'), 201);
    }

    public function destroy(Request $request, $paymentId, $commentId)
    {
        $business = auth()->user()->businessDetails;
        if (!$business) return response()->json(['message' => 'Business profile required'], 400);

        $payment = $business->payments()->findOrFail($paymentId);
        $comment = $payment->comments()->where('user_id', auth()->id())->findOrFail($commentId);

        $comment->delete();

        return response()->json(['message' => 'Comment deleted successfully']);
    }
}
