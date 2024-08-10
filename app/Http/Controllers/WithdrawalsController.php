<?php

namespace App\Http\Controllers;

use App\Models\balances;
use App\Models\transactions;
use App\Models\User;
use App\Models\withdrawals;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WithdrawalsController extends Controller
{
    //
    public function index()
    {
        // عرض جميع طلبات سحب الأرباح للمستخدم المحدد

        $withdrawals = withdrawals::with('user')->get();
        return response()->json($withdrawals);
    }


    public function show($id)
    {
        $withdrawal = withdrawals::find($id);
        if (!$withdrawal) {
            return response()->json(['error' => 'Withdrawal not found'], 404);
        }
        return response()->json($withdrawal);
    }

    public function process(Request $request)
    {

        $validated = $request->validate([
            // 'user_id' => 'required|integer',
            'selectedMethod' => 'required',
            'amount' => 'required|numeric|min:0.01',
        ]);


        $user = auth()->user();
        // Find or create the user's balance
        $balance = balances::firstOrCreate(
            ['user_id' => $user->id],
            ['total_balance' => 0, 'pending_balance' => 0, 'withdrawable_balance' => 0, 'total_earnings' => 0]
        );

        if ($balance->withdrawable_balance < $validated['amount']) {
            return response()->json(['success' => false, 'message' => 'Insufficient balance.'], 400);
        }

        // Create the withdrawal request
        withdrawals::create([
            'user_id' => $user->id,
            'amount' => $validated['amount'],
            'withdrawal_method	' => $validated['selectedMethod'],
            'withdrawal_date' => now(),
            'status' => 'pending',
        ]);
        // Create a new transaction for the withdrawal request
        transactions::create([
            'user_id' => $user->id,
            'amount' => $validated['amount'],
            'type' => 'withdrawal_request',
            'status' => 'pending',
            'details' => 'طلب سحب' . $validated['amount'],
        ]);

        return response()->json(['success' => true, 'message' => 'Withdrawal request submitted successfully.']);
    }

    public function approve($id)
    {
        $withdrawal = withdrawals::find($id);
        $transaction = transactions::find($withdrawal->id)->first();;

        if (!$withdrawal || $withdrawal->withdrawal_status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Invalid withdrawal request.'], 400);
        }

        // Find the user's balance
        $balance = balances::where('user_id', $withdrawal->user_id)->first();

        if ($balance->withdrawable_balance < $withdrawal->amount) {
            return response()->json(['success' => false, 'message' => 'Insufficient balance.'], 400);
        }


        // Deduct the amount from available balance and update the pending balance
        $balance->withdrawable_balance -= $withdrawal->amount;
        $balance->save();

        // Update withdrawal status
        $withdrawal->withdrawal_status = 'completed';
        $withdrawal->save();
        if ($transaction && $transaction->status === 'pending') {
            $transaction->status = 'completed';

            $transaction->amount = $balance->withdrawable_balance;
            $transaction->save();
        }

        return response()->json(['success' => true, 'message' => 'Withdrawal approved successfully.']);
    }
    public function reject($id)
    {
        $request = withdrawals::find($id);
        if ($request) {
            $request->withdrawal_status = 'cancelled';
            $request->save();
        }

        return response()->json(['status' => 'success']);
    }


    public function destroy($id)
    {
        $withdrawal = withdrawals::find($id);
        if (!$withdrawal) {
            return response()->json(['error' => 'Withdrawal not found'], 404);
        }

        $withdrawal->delete();

        return response()->json(['message' => 'Withdrawal deleted']);
    }
}
