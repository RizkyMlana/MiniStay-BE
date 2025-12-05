<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use App\Services\SupabaseService;


class PaymentController extends Controller
{

/**
 * @OA\Post(
 *     path="/api/user/payments",
 *     tags={"User - Payments"},
 *     summary="Upload payment proof & store payment request",
 *     security={{"userAuth":{}}},
 * 
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 required={"booking_id", "amount_requested"},
 *                 @OA\Property(property="booking_id", type="integer", example=12),
 *                 @OA\Property(property="amount_requested", type="integer", example=300000),
 *                 @OA\Property(property="amount_paid", type="integer", nullable=true, example=300000),
 *                 @OA\Property(property="bank_name", type="string", nullable=true, example="BCA"),
 *                 @OA\Property(property="bank_account", type="string", nullable=true, example="1234567890"),
 *                 @OA\Property(property="bank_owner", type="string", nullable=true, example="Deny Saputra"),
 *                 @OA\Property(property="proof", type="string", format="binary", nullable=true)
 *             )
 *         )
 *     ),
 * 
 *     @OA\Response(
 *         response=200,
 *         description="Payment stored successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="data", ref="#/components/schemas/Payment")
 *         )
 *     ),
 * 
 *     @OA\Response(
 *         response=422,
 *         description="Validation error"
 *     )
 * )
 */

    public function storePayment(Request $request){
        $data = $request->validate([
            'booking_id' => 'required',
            'amount_requested' => 'required|integer',
            'amount_paid' => 'nullable|integer',
            'bank_name' => 'nullable|string',
            'bank_account' => 'nullable|string',
            'bank_owner' => 'nullable|string',
            'proof' => 'nullable|file|image',
        ]);

        if($request->hasFile('proof')){
            $file = $request->file('proof');
            $filename = "payments/" . uniqid() . "." . $file->getClientOriginalExtension();

            $data['proof_url'] = SupabaseService::upload($file, $filename);
        }
    }

/**
 * @OA\Put(
 *     path="/api/admin/payments/{id}/status",
 *     tags={"Admin - Payments"},
 *     summary="Update payment status (admin only)",
 *     security={{"adminAuth":{}}},
 * 
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Payment ID",
 *         @OA\Schema(type="integer", example=10)
 *     ),
 * 
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"status"},
 *             @OA\Property(property="status", type="string", example="approved")
 *         )
 *     ),
 * 
 *     @OA\Response(
 *         response=200,
 *         description="Payment status updated",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Payment status updated")
 *         )
 *     ),
 * 
 *     @OA\Response(
 *         response=404,
 *         description="Payment not found"
 *     )
 * )
 */

    public function updatePaymentStatus(Request $request, $id){
        $payment = Payment::findOrFail($id);
        $payment->status = $request->status;
        $payment->save();

        return response()->json(['message' => 'Payment status updated']);
    }

/**
 * @OA\Get(
 *     path="/api/user/my-payments",
 *     tags={"User - Payments"},
 *     summary="Get all payments of authenticated user",
 *     security={{"userAuth":{}}},
 * 
 *     @OA\Response(
 *         response=200,
 *         description="List of user payments",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(ref="#/components/schemas/Payment")
 *         )
 *     )
 * )
 */


    public function myPayments(){
        $data = Payment::whereHas('booking', fn($q) => 
            $q->where('user_id', auth()->guard('user')->id())
        )->get();

        return response()->json($data);
    }
}
