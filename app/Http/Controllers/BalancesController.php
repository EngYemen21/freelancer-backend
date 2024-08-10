<?php

namespace App\Http\Controllers;

use App\Models\balances;
use Illuminate\Http\Request;

class BalancesController extends Controller
{
    //
    public function getUserBalance(Request $request)
    {
        $user = auth()->user();
        $balance = balances::where('user_id', $user->id)->first();

        if (!$balance) {
            return response()->json(['error' => 'User balance not found'], 404);
        }

        return response()->json($balance);
    }
    public function updateBalance(Request $request)
    {
        $user = auth()->user();
        $balance = balances::where('user_id', $user->id)->first();

        if (!$balance) {
            return response()->json(['error' => 'User balance not found'], 404);
        }

        $data = $request->validate([
            'total_balance' => 'required|numeric|min:0',
            'withdrawable_balance' => 'required|numeric|min:0',
            'pending_balance' => 'required|numeric|min:0',
            'total_earnings' => 'required|numeric|min:0',
        ]);

        $balance->update($data);

        return response()->json(['message' => 'Balance updated successfully']);
    }
}
