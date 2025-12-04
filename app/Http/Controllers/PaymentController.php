<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use App\Services\SupabaseService;


class PaymentController extends Controller
{
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
    public function updatePaymentStatus(Request $request, $id){
        $payment = Payment::findOrFail($id);
        $payment->status = $request->status;
        $payment->save();

        return response()->json(['message' => 'Payment status updated']);
    }

    public function myPayments(){
        $data = Payment::whereHas('booking', fn($q) => 
            $q->where('user_id', auth()->id())
        )->get();

        return response()->json($data);
    }
}
