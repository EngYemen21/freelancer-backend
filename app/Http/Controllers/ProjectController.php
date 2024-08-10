<?php

namespace App\Http\Controllers;

use Mail;
use Carbon\Carbon;
use App\Models\Bid;
use App\Models\User;
use App\Models\Project;

use App\Models\Category;
use App\Events\BidCreated;
use App\Models\AcceptedBid;
use App\Models\ChatProject;
use Illuminate\Http\Request;
use App\Models\ProjectStatus;
use App\Models\Notificationes;
use App\Events\AcceptBidsEvent;
use App\Models\NotifcationProject;
use App\Notifications\BidAccepted;
use Illuminate\Support\Facades\Http;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ProjectApprovalRequest;
use App\Events\ProjectDeliveryRequestedProject;




class ProjectController extends Controller
{


    public function index(Request $request)
    {
        $filterProject = $request->query('selectedCategory');
        if ($filterProject) {

            $categoryIdsArray = explode(',', $filterProject);
            $projects = Project::with('bids', 'client', 'status', 'category')->whereIn('category_id', $categoryIdsArray)->latest()->get();
            return response()->json($projects);
        } else {
            $projects = Project::with('bids', 'bids.freelancer', 'client', 'status', 'category')->where('status', 'completed')->latest()->get();
            return response()->json($projects);
        }
    }
    public function projectPending()
    {
        $projects = Project::with('bids', 'bids.freelancer', 'client', 'status', 'category')->where('status', 'pending')->latest()->get();
        return response()->json($projects);
    }

    public function show($id)
    {
        $project = Project::with(['client', 'bids', 'category', 'status'])->findOrFail($id);
        return response()->json(['project' => $project]);
    }
    public function freelancerShowProject($id)
    {
        $project = Project::with(['client', 'bids', 'category', 'status', 'conversations', 'acceptedBid'])->where('user_id', $id)->get();
        return response()->json(['project' => $project]);
    }
    public function getAcceptedBids($id)
    {
        $project = AcceptedBid::with(['conversations', 'project'])->where('freelancer_id', $id)->get();
        return response()->json(['project' => $project]);
    }


    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'budget' => 'required|numeric',
            'skills' => 'required|array',
            'dateTime' => 'required',
            'category_id' => 'required'
        ]);
        // إنشاء مشروع جديد
        $project = Project::create([
            'title' => $validatedData['title'],
            'description' => $validatedData['description'],
            'budget' => $validatedData['budget'],
            'dateTime' => Carbon::now(),

            'user_id' => auth()->user()->id,
            'category_id' => $validatedData['category_id'],
        ]);
        foreach ($validatedData['skills'] as $skill) {
            $project->skills()->create(['name' => $skill]);
        }
        if ($project) {
            $projectStatus = ProjectStatus::updateOrCreate(
                ['project_id' => $project->id],
                [
                    'status' => 'pending',
                    'status_changed_at' => Carbon::now()
                ]
            );
        }
        $admin = User::where('role', 1)->first();
        if ($admin) {
            Notification::send($admin, new ProjectApprovalRequest($project));
        }

        return response()->json(['project' => $project, 'projectStatus' => $projectStatus], 201);
    }

    public function update(Request $request, $id)
    {
        $project = Project::findOrFail($id);
        $project->update($request->all());
        return response()->json(['project' => $project]);
    }

    public function approveProject(Request $request, $id)
    {
        $project = Project::findOrFail($id);
        $project->status = 'completed';
        $project->save();
        // $client = User::findOrFail($project->client_id);
        $CreateNotificationProjects = Notificationes::create([
            'user_id' => $project->user_id,
            'type' => 'Notification',
            'message' => ' تم الموافقه على نشر المشروع ' . $project->title,
            'notifiable_id' => $project->id,
            'notifiable_type' => Project::class,
        ]);
        return response()->json(['message' => 'Project approved successfully']);
    }

    public function rejectProject(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        // رفض نشر المشروع مع إرسال سبب الرفض
        $reason = $request->reason;
        // يمكنك هنا إرسال بريد إلكتروني إلى العميل بسبب الرفض

        return response()->json(['message' => 'Project rejected successfully']);
    }

    public function destroy($id)
    {
        $project = Project::findOrFail($id);
        $project->delete();
        return response()->json(null, 204);
    }

    public function IndexAcceptedBids()
    {
        $acceptedBids = AcceptedBid::with('project', 'bid', 'freelancer', 'project.client')->get();
        return response()->json($acceptedBids);
    }

    // public function acceptBid(Request $request,  $id)
    // {
    //     $acceptedBid = AcceptedBid::create([
    //         'project_id' => $id,
    //         'bid_id' => $request->bidID,
    //         'freelancer_id' => $request->freelancerID,
    //         'accepted_at' => Carbon::now(),
    //     ]);
    //     $projectStatus = ProjectStatus::updateOrCreate(
    //         ['project_id' => $id],
    //         [
    //             'status' => 'In Progress',
    //             'status_changed_at' => Carbon::now()
    //         ]
    //     );


    //     $acceptedBid = AcceptedBid::find($acceptedBid->id);
    //     $chat = ChatProject::where('project_id', $id)->where('chatAcceptedBid_id', $acceptedBid->id)->first();
    //     if (!$chat) {

    //         $chat = ChatProject::create([

    //             'chatAcceptedBid_id' => $acceptedBid->id, //6
    //             'project_id' => $id,

    //             'freelancer_id' => $request->freelancerID, //4

    //         ]);
    //         $chat = ChatProject::find($chat->id);
    //         event(new AcceptBidsEvent($acceptedBid));
    //         return response()->json(['message' => 'Bid accepted and project is now in progress.', 'chat' => $chat, 'projectStatus' => $projectStatus, 'acceptedBid' => $acceptedBid]);
    //     }
    //     $bid = Bid::find($request->bidID);
    //     $freelancer = User::find($bid->freelancer_id);
    //     $bid->status = 'accepted';
    //     $bid->save();
    //     event(new AcceptBidsEvent($acceptedBid));
    //     return response()->json(['message' => 'Bid accepted and project is now in progress.', 'chat' => $chat, 'projectStatus' => $projectStatus, 'acceptedBid' => $acceptedBid]);
    // }
    public function rejectBid(Request $request, $bidId)
    {
        $bid = Bid::find($bidId);

        // Update bid status
        $bid->status = 'rejected';
        $bid->save();

        return response()->json(['message' => 'Bid rejected.']);
    }
    public function startProject(Request $request, $bidId)
    {
        $bid = Bid::find($bidId);

        // Update bid status
        $bid->status = 'in_progress';
        $bid->save();

        return response()->json(['message' => 'Project started.']);
    }


    public function requestDelivery(Request $request, $id)
    {


        $project = Project::find($id);
        if (!$project) {
            return response()->json(['message' => 'Project not found'], 404);
        }
        $message = 'فضلا اقبل طلب استلام' . $project->title;
        $projectStatus = ProjectStatus::where('project_id', $id)->firstOrFail();
        $projectStatus->update([
            'status' => $request->awaiting_confirmation,
            'status_changed_at' => now(),
        ]);
        // $receiverId = ($chat->user1_id == auth()->user()->id) ? $chat->user2_id : $chat->user1_id;
        $CreateNotificationProjects = Notificationes::create([
            'user_id' => $project->user_id,
            'type' => 'Notification',
            'message' => '  طلب استلام مشروع  ' . $project->title,
            'notifiable_id' => $project->id,
            'notifiable_type' => ProjectStatus::class,
        ]);

        // إرسال إشعار للعميل
        event(new ProjectDeliveryRequestedProject($project, $message));

        return response()->json(['UpdateStatus' => $CreateNotificationProjects, 'project' => $project, 'ProjectStatus' => $projectStatus, 'message' => 'Delivery request sent successfully']);
    }
    public function GetStatusDelivery(Request  $request, $id)
    {
        $awitaingStatus = $request->awaiting_confirmation;

        $project = Project::find($id);
        if (!$project) {
            return response()->json(['message' => 'Project not found'], 404);
        }
        $projectStatus = ProjectStatus::where('project_id', $id)->firstOrFail();

        return response()->json(['ProjectStatus' => $projectStatus, 'project' => $project]);
    }



    public function approveDelivery(Request $request, Project $project, $projectId)
    {
        $freelancerID = $request->freelancerID;
        $project = Project::find($projectId);
        if (!$project) {
            return response()->json(['message' => 'Project not found'], 404);
        }
        $projectStatus = ProjectStatus::where('project_id', $projectId)->firstOrFail();
        $projectStatus->update([
            'status' => 'Completed',
            'status_changed_at' => now(),
        ]);
        $receiverId = ($freelancerID == auth()->user()->id) ? $projectId->id : $freelancerID;
        $CreateNotificationProjects = Notificationes::create([
            'user_id' =>  $receiverId,
            'type' => 'message',
            'message' => '  تم الموافقة لاستلام المشروع بنجاح ' . $project->title,
            'notifiable_id' => $projectStatus->id,
            'notifiable_type' => ProjectStatus::class,
        ]);


        return response()->json(['UpdateCompletedProject' => $CreateNotificationProjects, 'message' => 'Project status updated.', 'projectStatus' => $projectStatus]);
    }
}
