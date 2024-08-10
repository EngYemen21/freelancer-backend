<?php

namespace App\Http\Controllers;

use App\Models\IssueReport;
use App\Events\MessageEvent;
use Illuminate\Http\Request;
use App\Models\Conversatione;
use App\Models\Notificationes;

class IssueReportController extends Controller
{
    //
    public function index()
    {
        $reports = IssueReport::with(['user', 'conversation.messages'])->get();
        return response()->json($reports);
    }

    // إرسال ردود المدير على التقارير
    public function reply(Request $request, $conversationId)
    {
        $validatedData = $request->validate([
            'message' => 'required|string',
        ]);

        $userID = auth()->user()->id; // افتراض أن المدير هو المستخدم الحالي
        $conversation = Conversatione::findOrFail($conversationId);

        // قم بإنشاء رسالة جديدة
        $message = $conversation->messages()->create([
            'user_id' => $userID,
            'content' => $validatedData['message'],
        ]);

        $receiverId = ($conversation->user1_id == $userID) ? $conversation->user2_id : $conversation->user1_id;
        Notificationes::create([
            'user_id' => $receiverId,
            'type' => 'message',
            'message' => 'لديك إشعار جديد في المحادثة.',
            'notifiable_id' => $conversation->id,
            'notifiable_type' => Conversatione::class,
        ]);

        event(new MessageEvent($conversation->id, $message));

        return response()->json(['message' => $message, 'conversation' => $conversation->id]);
    }



    public function store(Request $request)
    {
        $user1_id = auth()->user()->id;
        $admin_id = 1;
        $IssueReport = IssueReport::where('user_id', $user1_id)->where('admin_id', $admin_id)->first();

        if (!$IssueReport) {


            $issueReport = IssueReport::create([
                'user_id' => auth()->user()->id,
                'admin_id' => 1, // You may assign admin dynamically or leave it null initially
                'conversation_id' => null, // Initialize conversation_id
                'message' => $request->message,
                'status' => 'pending', // Initial status
            ]);
            $conversation = Conversatione::create([
                'user1_id' => auth()->user()->id,
                'user2_id' => $issueReport->admin_id, // Admin id, if assigned later
                'conversable_id' => $issueReport->id,
                'conversable_type' => 'App\Models\IssueReport',
            ]);

            $issueReport->update(['conversatione_id' => $conversation->id]);
            $message = $conversation->messages()->create([
                'user_id' => auth()->user()->id,
                'content' =>  $request->message,
            ]);

            return response()->json(['issueReport' => $issueReport, 'conversation' => $conversation]);
        } else {
            $this->addMessage($request, $IssueReport,  $request->message);
        }
    }

    public function addMessage(Request $request, $conversationId, $message)
    {
        $userID = auth()->user()->id;
        $conversation = Conversatione::findOrFail($conversationId->conversatione_id);
        $message = $conversation->messages()->create([
            'user_id' => auth()->user()->id,
            'content' =>  $message,
            'conversatione_id' => $conversation->id
        ]);


        // إرسال إشعار للطرف الآخر في المحادثة
        $receiverId = ($conversation->user1_id == auth()->user()->id ? $conversation->user2_id : $conversation->user1_id);
        Notificationes::create([
            'user_id' => $receiverId,
            'type' => 'message',
            'message' => 'لديك إخطار جديدة  من الدعم الفني.',
            'notifiable_id' => $conversationId->id,
            'notifiable_type' => IssueReport::class,
        ]);


        return response()->json(['message' => $message, 'conversation' => $conversation]);
    }
    public function show($id)
    {
        $issue = IssueReport::with(['user', 'conversation.messages'])->where('user_id', $id)->where('admin_id', 1)->get();
        return response()->json(['issue' => $issue]);
    }

    public function deleteIfEmpty(Request $request, $id)
    {
        $conversation = Conversatione::findOrFail($id);

        // Check if there are no messages in the conversation
        if ($conversation->issueReports()->count() == 0) {
            $conversation->delete();
            return response()->json(['message' => 'Conversation deleted successfully']);
        }

        return response()->json(['message' => 'Conversation has messages and cannot be deleted']);
    }
}
