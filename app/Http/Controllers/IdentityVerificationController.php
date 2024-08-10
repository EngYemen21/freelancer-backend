<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\IdentityVerification;
use Illuminate\Support\Facades\Auth;

class IdentityVerificationController extends Controller
{
    //
    public function store(Request $request)
    {
        $request->validate([
            'front_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'back_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $frontImagePath = $request->file('front_image')->store('identity_verifications');
        $backImagePath = $request->file('back_image')->store('identity_verifications');

        IdentityVerification::create([
            'user_id' => Auth::id(),
            'front_image' => $frontImagePath,
            'back_image' => $backImagePath,
            'status' => 'pending',
        ]);

        return response()->json(['message' => 'Identity verification submitted successfully.']);
    }

    public function index()
    {
        $verifications = IdentityVerification::where('status', 'pending')->get();
        return response()->json($verifications);
    }

    public function updateStatus($id, Request $request)
    {
        $verification = IdentityVerification::findOrFail($id);
        $verification->status = $request->status;
        $verification->save();

        return response()->json(['message' => 'Status updated successfully.']);
    }
}
