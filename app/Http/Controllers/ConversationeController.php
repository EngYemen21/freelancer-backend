<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Bid;
use App\Models\User;
use App\Models\Project;
use App\Models\AcceptedBid;
use App\Events\MessageEvent;
use Illuminate\Http\Request;
use App\Models\Conversatione;
use App\Models\ProjectStatus;
use App\Models\Notificationes;
use Illuminate\Support\Facades\Auth;
use App\Events\ProjectDeliveryRequestedProject;

class ConversationeController extends Controller
{



    public function index()
    {
        $projects = Project::with('bids', 'client', 'status', 'categories')->latest()->get();
        return response()->json($projects);
    }

    public function show($id)
    {
        $project = Project::with(['client', 'bids', 'categories', 'status'])->findOrFail($id);
        return response()->json(['project' => $project]);
    }
    public function freelancerShowProject($id)
    {
        $project = Project::with(['client', 'bids', 'categories', 'status', 'acceptedBid.ChatacceptedBid'])->where('user_id', $id)->get();
        return response()->json(['project' => $project]);
    }
    public function getAcceptedBids($id)
    {
        $project = AcceptedBid::with(['ChatacceptedBid', 'project'])->where('freelancer_id', $id)->get();
        return response()->json(['project' => $project]);
    }



    public function approveProject(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        // تأكيد الموافقة على نشر المشروع
        $project->status = 'completed';
        $project->save();

        // إرسال إشعار للعميل بالموافقة
        $client = User::findOrFail($project->client_id);
        // Mail::to($client->email)->send(new ProjectApprovedNotification($project));

        return response()->json(['message' => 'Project approved successfully']);
    }

    public function rejectProject(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        $reason = $request->reason;

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
    public function Conversionindex(Request $request)
    {


        $conversations = Conversatione::with(['user1', 'user2'])->where('user2_id', auth()->user()->id)->orWhere('user1_id', auth()->user()->id)->get();


        return response()->json($conversations);
    }


    public function getConversion($ConversionId)
    {
        $getConversion = Conversatione::where('id', $ConversionId)->get();
        return response()->json($getConversion);
    }

    public function getNotification()
    {
        $user = Auth::user();
        $notifications = Notificationes::where('user_id', $user->id)->where('read', 0)->get();
        return response()->json($notifications);
    }
    public function markAsRead($id)
    {

        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->read = 1;
        $notification->save();

        return response()->json(['message' => 'تم وضع الإشعار كمقروء']);
    }



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
        $message = 'فضلا اقبل طلب استلام';
        $projectStatus = ProjectStatus::where('project_id', $id)->firstOrFail();
        $projectStatus->update([
            'status' => $request->awaiting_confirmation,
            'status_changed_at' => now(),
        ]);

        // إرسال إشعار للعميل
        event(new ProjectDeliveryRequestedProject($project, $message));

        return response()->json(['ProjectStatus' => $projectStatus, 'message' => 'Delivery request sent successfully']);
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
        $projectStatus = ProjectStatus::where('project_id', $projectId)->firstOrFail();
        $projectStatus->update([
            'status' => 'Completed',
            'status_changed_at' => now(),
        ]);


        return response()->json(['message' => 'Project status updated.', 'projectStatus' => $projectStatus]);
    }


    public function addMessage(Request $request, $conversationId)
    {
        // $userID=auth()->user()->id;
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'content' => 'required|string',
            // 'attachment'=>'string|nullbale',
        ]);

        $conversation = Conversatione::findOrFail($conversationId);
        $message = $conversation->messages()->create($validatedData);
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->move('Image_Services', $fileName);
            // $file->storeAs('/public/chat-attechments',$fileName);
            $fileChat = $message->attechment()->create([
                'filename' => $fileName,
                'file_path' => $filePath,
                'file_type' => $file->getClientMimeType(),
                'file_size' => 0,
                // 'file_size'=>$file->getSize() ,
            ]);
        }
        // $attachment = $conversation->attachment()->create($validatedData['attachment']);

        // إرسال إشعار للطرف الآخر في المحادثة
        $receiverId = ($conversation->user1_id == $validatedData['user_id']) ? $conversation->user2_id : $conversation->user1_id;
        Notificationes::create([
            'user_id' => $receiverId,
            'type' => 'message',
            'message' => 'لديك رسالة جديدة في المحادثة.',
            'notifiable_id' => $conversation->id,
            'notifiable_type' => Conversatione::class,
        ]);
        // $message = "ارسل لك " . Auth::user()->firstName . "لتنفيذ الخدمة";
        event(new MessageEvent($conversation->id, $message));


        return response()->json(['message' => $message, 'conversation' => $conversation->id]);
    }

    public function getMessages($conversationId)
    {
        $conversation = Conversatione::with('messages.user', 'messages.attechment')->findOrFail($conversationId);
        return response()->json($conversation);
    }
    public function showChatFromNotification($id)
    {
        $chat = Conversatione::with('project', 'conversable', 'project.acceptedBid')->where('project_id', $id)->first();

        return response()->json(['chat' => $chat]);
    }
}
