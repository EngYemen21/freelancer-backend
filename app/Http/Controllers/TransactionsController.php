<?php

namespace App\Http\Controllers;

use App\Models\transactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TransactionsController extends Controller
{
    //

    public function getUserTransactions()
    {
        $userId = Auth::id();
        $transactions = transactions::where('user_id', $userId)->get();
        if (!$transactions) {
            return response()->json(['error' => "not found"]);
        }

        return response()->json($transactions);
    }

    public function store(Request $request)
    {
        $user = auth()->id();

        $validatedData = [
            'sender_name' => $request->input('sender_name'),
            'user_id' => $user,
            'sender_whatsapp' => $request->input('sender_whatsapp'),
            'amount' => $request->input('amount'),
            'transaction_number' => $request->input('transaction_number'),
            'transaction_document' => $request->file('transaction_document')
        ];

        $rules = [
            'sender_name' => 'required|string',
            'sender_whatsapp' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'transaction_number' => 'required|string|max:255',
            'transaction_document' => 'required|file|max:2048|mimes:pdf,doc,docx,jpg,png,gif'
        ];

        $validator = Validator::make($validatedData, $rules);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        if ($user) {
            if ($request->hasFile('transaction_document')) {
                $image = $request->file('transaction_document');
                $filename = time() . '_' . $image->getClientOriginalName();
                $imageUrl =  $image->move('Image_Services', $filename);
                $validatedData['transaction_document'] = $imageUrl;
            } else {
                throw new Exception('Error uploading image. Please ensure a valid image is selected.', 422); // 422: Unprocessable Entity
            }


            $transaction = transactions::create($validatedData);

            return response()->json($transaction, 201);
        }
    }
}
