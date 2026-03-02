<?php

namespace App\Http\Controllers\Zilmoney;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PaymentAttachmentController extends Controller
{
    public function index(Request $request, $paymentId)
    {
        $business = auth()->user()->businessDetails;
        if (!$business) return response()->json(['message' => 'Business profile required'], 400);

        $payment = $business->payments()->findOrFail($paymentId);

        return response()->json($payment->attachments()->with('user')->get());
    }

    public function store(Request $request, $paymentId)
    {
        $business = auth()->user()->businessDetails;
        if (!$business) return response()->json(['message' => 'Business profile required'], 400);

        $payment = $business->payments()->findOrFail($paymentId);

        $validated = $request->validate([
            'file_path' => 'required|url',
            'original_name' => 'nullable|string|max:255'
        ]);

        $attachment = $payment->attachments()->create([
            'user_id' => auth()->id(),
            'file_path' => $validated['file_path'],
            'original_name' => $validated['original_name']
        ]);

        return response()->json($attachment->load('user'), 201);
    }

    public function destroy(Request $request, $paymentId, $attachmentId)
    {
        $business = auth()->user()->businessDetails;
        if (!$business) return response()->json(['message' => 'Business profile required'], 400);

        $payment = $business->payments()->findOrFail($paymentId);
        $attachment = $payment->attachments()->findOrFail($attachmentId);

        $attachment->delete();

        return response()->json(['message' => 'Attachment deleted successfully']);
    }
}
