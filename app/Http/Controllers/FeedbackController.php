<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    //
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'accepted_bids_id' => 'required|exists:accepted_bids,id',
            'contracts_id' => 'required|exists:contracts,id',
            'rating_id' => 'required|exists:ratings,id',
            'comment' => 'nullable|string',
        ]);

        $feedback = Feedback::create($validatedData);

        return response()->json($feedback, 201);
    }

    // عرض جميع التعليقات
    public function index()
    {
        $feedbacks = Feedback::with('acceptedBid', 'contract', 'rating')->get();
        return response()->json($feedbacks);
    }

    // عرض تعليق معين
    public function show($id)
    {
        $feedback = Feedback::with('acceptedBid', 'contract', 'rating')->findOrFail($id);
        return response()->json($feedback);
    }


    public function getFeedbacksByContract($contractId)
    {
        $feedbacks = Feedback::where('contracts_id', $contractId)
            ->with('rating', 'acceptedBid', 'contract')
            ->get();

        return response()->json($feedbacks);
    }


    // تحديث تعليق معين
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'accepted_bids_id' => 'required|exists:accepted_bids,id',
            'contracts_id' => 'required|exists:contracts,id',
            'rating_id' => 'required|exists:ratings,id',
            'comment' => 'nullable|string',
        ]);

        $feedback = Feedback::findOrFail($id);
        $feedback->update($validatedData);

        return response()->json($feedback);
    }

    // حذف تعليق معين
    public function destroy($id)
    {
        $feedback = Feedback::findOrFail($id);
        $feedback->delete();

        return response()->json(null, 204);
    }
}
