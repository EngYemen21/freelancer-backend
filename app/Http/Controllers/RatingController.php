<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Rating;
use App\Models\Comment;
use App\Models\Project;
use App\Models\Contract;
use App\Models\Feedback;
use App\Models\Services;
use App\Models\AcceptedBid;
use Illuminate\Http\Request;

class RatingController extends Controller
{



    public function show(Services $service, $id)
    {
        $service = Services::find($id);
        $ratings = $service->ratings()->get();
        return response()->json($ratings);
    }
    public function storeComment(Request $request, $ratingId)
    {
        $projectId = $request->input('project_id');
        $serviceId = $request->input('service_id');

        if ($projectId) {
            $commentable = Project::find($projectId);
            $type = 'project';
            $id = $projectId;
        } elseif ($serviceId) {
            $commentable = Services::find($serviceId);
            $type = 'service';
            $id = $serviceId;
        } else {
            return response()->json(['error' => 'Invalid project or service ID'], 400);
        }

        // التحقق من صحة البيانات المُدخلة
        $validatedData = $request->validate([
            'comment' => 'required|string',
        ]);

        // التحقق مما إذا كان المشروع أو الخدمة موجودًا
        if (!$commentable) {
            return response()->json(['error' => ucfirst($type) . ' not found'], 404);
        }

        // إنشاء تعليق جديد
        $comment = new Comment();
        $comment->comment = $validatedData['comment'];
        $comment->user_id = auth()->id();

        // حفظ التعليق للكيان المناسب
        $commentable->comments()->save($comment);

        return response()->json(['message' => 'Comment added successfully'], 201);
    }

    //////store reating for services
    public function storeRating(Request $request)
    {


        $acceptedBids_id = $request->input('project_id');
        $contract_id = $request->input('contract_id');

        if ($acceptedBids_id) {
            $rateable = Project::find($acceptedBids_id);
            $rateableType = Project::class;
        } elseif ($contract_id) {
            $rateable = Contract::find($contract_id);
            $rateableType = Contract::class; //
        } else {
            return response()->json(['error' => 'Invalid project or service ID'], 400);
        }
        $user = auth()->user()->id;


        $rating = Rating::create([

            'client_id' => auth()->id(),
            'quality_score' => $request->quality_score,
            'delivery_speed_score' => $request->delivery_speed_score,
            'communication_score' => $request->communication_score,
            'deadline_adherence_score' => $request->deadline_adherence_score,
            'overall_score' => $request->overall_score,
            'comment' => $request->comment,
            'rateable_id' => $rateable->id,
            'rateable_type' => $rateableType,
        ]);

        $rating->save();
        $acceptedBidExists = $acceptedBids_id ? AcceptedBid::find($acceptedBids_id) : null;
        $contract_idExists = $contract_id ? Contract::find($contract_id) : null;
        $feedback = Feedback::create([

            'contracts_id' => $contract_idExists ? $contract_id : null,
            'accepted_bids_id' => $acceptedBidExists ? $acceptedBids_id : null,
            'rating_id' => $rating->id,

        ]);
        $feedback->save();


        return response()->json(['rating' => $rating, 'feedback' => $feedback], 201);
    }
    public function getRatingUser($userId)
    {
        // جلب جميع التقييمات المرتبطة بالمستخدم
        $ratings = Rating::where('client_id', $userId)->get();

        // التحقق من وجود تقييمات
        if ($ratings->isEmpty()) {
            return [
                'quality_score' => 0,
                'delivery_speed_score' => 0,
                'communication_score' => 0,
                'deadline_adherence_score' => 0,
                'overall_score' => 0,
            ];
        }

        // حساب متوسط التقييمات
      
        $averageOverallScore = $ratings->avg('overall_score');

        // إرجاع متوسط التقييمات
        return [

            'overall_score' => $averageOverallScore,
        ];
    }
    public function getRatings(Request $request, $clientID)
    {

        $ratings = Rating::with('rateable', 'feedback', 'user', 'feedback.contract.freelancer')->where('client_id', $clientID)->get();
        return response()->json(['ratings' => $ratings]);
    }
}
