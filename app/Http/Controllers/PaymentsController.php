<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Project;
use App\Models\balances;
use App\Models\Contract;
use App\Models\payments;
use App\Models\Services;
use App\Models\AcceptedBid;
use App\Models\transactions;
use Illuminate\Http\Request;
use App\Models\Conversatione;
use App\Models\ProjectStatus;
use App\Models\Notificationes;
use App\Events\AcceptBidsEvent;
use Illuminate\Support\Facades\Validator;

class PaymentsController extends Controller
{
    //
    public function index()
    {
        $payments = payments::with('user')->get();
        return response()->json($payments);
    }
    public function process(Request $request)
    {


        if ($request->type == 'project') {

            $validator = Validator::make($request->all(), [
                'projectID' => 'required',
                'user_id' => 'required|exists:users,id',
                'payment_method' => 'required',
                'amount' => 'required|numeric|min:0',
            ]);
            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->first()], 400);
            }
            $payment = payments::create([
                // 'contract_id' => 1,
                'user_id' => $request->user_id,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'payment_status' => 'pending',
                'payment_data' => 'Platform',
                'manual_payment' => $request->payment_method === 'manual',
                'morphable_id' => $request->projectID,
                'morphable_type' => Project::class,

            ]);
            if (!$payment) {

                return response()->json(['success' => false, 'message' => 'Payment creation failed.'], 500);
            }
            $acceptedBid = AcceptedBid::create([
                'project_id' => $request->projectID,
                'bid_id' => $request->bidID,
                'freelancer_id' => $request->freelancerID,
                'accepted_at' => Carbon::now(),
            ]);
            $projectStatus = ProjectStatus::updateOrCreate(
                ['project_id' => $request->projectID], //
                [
                    'status' => 'In Progress',
                    'status_changed_at' => Carbon::now()
                ]
            );
            $payment->save();
            if ($request->payment_method === 'manual') {
                $payment->payment_status  = 'pending_admin_approval';

                return response()->json(['success' => true, 'message' => 'Payment is pending admin approval.']);
            } else {
                switch ($request->payment_method) {
                    case 'platform':
                        $balance = balances::where('user_id', $request->user_id)->first();
                        if ($balance && $balance->total_balance >= $request->amount) {
                            $balance->total_balance -= $request->amount;
                            $balance->pending_balance += $request->amount;
                            $balance->save();
                            $payment->payment_status = 'paid';
                        } else {
                            $payment->payment_status  = 'failed';
                            $payment->save();
                            return response()->json(['success' => false, 'message' => 'Insufficient balance.'], 400);
                        }
                        break;
                    case 'kareemee':
                        $payment->payment_status  = 'paid';
                        break;
                    case 'paypal':
                        $payment->payment_status  = 'paid';
                        break;
                    default:
                        $payment->payment_status  = 'failed';
                        $payment->save();
                        return response()->json(['success' => false, 'message' => 'Invalid payment method.'], 400);
                }


                $payment->save();
                if ($payment->payment_status  == 'paid') {
                    $acceptedBid->save();
                    $project = Project::find($request->projectID);
                    $acceptedBid = AcceptedBid::find($acceptedBid->id);
                    $chat = $acceptedBid->conversations()->create([
                        'user1_id' => auth()->user()->id,
                        'project_id' => $request->projectID,
                        'user2_id' => $request->freelancerID,
                    ]);
                    $receiverId = ($chat->user1_id == auth()->user()->id) ? $chat->user2_id : $chat->user1_id;
                    $CreateNotificationProjects = Notificationes::create([
                        'user_id' => $receiverId,
                        'type' => 'message',
                        'message' => '   لقد قبل عرضك في مشروع   ' . $project->title,
                        'notifiable_id' => $chat->id,
                        'notifiable_type' => Conversatione::class,
                    ]);
                    // Log transaction for buyer
                    $chat = Conversatione::find($chat->id);
                    transactions::create([
                        'user_id' => auth()->user()->id,
                        'amount' => $payment->amount,
                        'type' => 'payment',
                        'status' => 'completed',
                        'details' => 'دفع  للتنفيذ مشروع ' . $project->title,
                    ]);
                    // Log transaction for seller
                    transactions::create([
                        'user_id' => $request->freelancerID,
                        'amount' => $payment->amount,
                        'type' => 'Implements Project',
                        'status' => 'completed',
                        'details' => ' حصول على الرصيد مقابل تنفيذ مشروع' . $project->title,
                    ]);

                    event(new AcceptBidsEvent($CreateNotificationProjects));
                    return response()->json(['success' => true, 'message' => 'Bid accepted and project is now in progress.', 'chat' => $chat, 'projectStatus' => $projectStatus, 'acceptedBid' => $acceptedBid]);
                } else {
                    // If payment failed, delete the contract
                    $acceptedBid->delete();
                }
            }
        } else {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'service_id' => 'required|exists:services,id',
                'user_id' => 'required|exists:users,id',
                'payment_method' => 'required',
                'amount' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->first()], 400);
            }
            $payment = payments::create([
                // 'contract_id' => 1,
                'user_id' => $request->user_id,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'payment_status' => 'pending',
                'payment_data' => 'Platform',
                'manual_payment' => $request->payment_method === 'manual',
                'morphable_id' => $request->service_id,
                'morphable_type' => Services::class,
            ]);
            if (!$payment) {

                return response()->json(['success' => false, 'message' => 'Payment creation failed.'], 500);
            }
            $serviceDeliveryTime = Services::findOrFail($request->service_id);

            $contract = Contract::create([
                'service_id' => $request->service_id,
                'payment_id' => $payment->id,
                'client_id' => $request->user_id,
                'freelancer_id' => Services::find($request->service_id)->user_id,
                'price' => $request->amount,
                'start_date' => now(),
                'end_date' => now()->addDays($serviceDeliveryTime->delivery_time),
                ///payments
                'status' => 'pending', // Initial status
            ]);

            if ($request->payment_method === 'manual') {
                $payment->payment_status  = 'pending_admin_approval';
                $payment->save();
                return response()->json(['success' => true, 'message' => 'Payment is pending admin approval.']);
            } else {
                switch ($request->payment_method) {
                    case 'platform':
                        $balance = balances::where('user_id', $request->user_id)->first();
                        if ($balance && $balance->total_balance >= $request->amount) {
                            $balance->total_balance -= $request->amount;
                            $balance->pending_balance += $request->amount;
                            $balance->save();
                            $payment->payment_status = 'paid';
                        } else {
                            $payment->payment_status  = 'failed';
                            $payment->save();
                            return response()->json(['success' => false, 'message' => 'Insufficient balance.'], 400);
                        }
                        break;
                    case 'kareemee':
                        $payment->payment_status  = 'paid';
                        break;
                    case 'paypal':
                        $payment->payment_status  = 'paid';
                        break;
                    default:
                        $payment->payment_status  = 'failed';
                        $payment->save();
                        return response()->json(['success' => false, 'message' => 'Invalid payment method.'], 400);
                }


                $payment->save();
                if ($payment->payment_status  == 'paid') {
                    $contract->status = 'active';
                    ///هنا نحسب الوقت
                    $contract->save();
                    // $createConversionService=
                    $chat = $contract->conversations()->create([
                        'user1_id' => auth()->user()->id,
                        'service_id' => $request->service_id,
                        'user2_id' =>   $contract->freelancer_id,


                    ]);
                    $title = Services::find($request->service_id)->title;
                    $receiverId = ($chat->user1_id == auth()->user()->id) ? $chat->user2_id : $chat->user1_id;
                    $CreateNotificationProjects = Notificationes::create([
                        'user_id' => $receiverId,
                        'type' => 'message',
                        'message' => '  البدء بتنفيذ الخدمة' .  $title,
                        'notifiable_id' => $chat->id,
                        'notifiable_type' => Conversatione::class,
                    ]);

                    $chat = Conversatione::find($chat->id);

                    transactions::create([
                        'user_id' => auth()->user()->id,
                        'amount' => $payment->amount,
                        'type' => 'payment',
                        'status' => 'completed',
                        'details' => 'دفع لشراء خدمة' . $title,
                    ]);
                    // Log transaction for seller
                    transactions::create([
                        'user_id' => $contract->freelancer_id,
                        'amount' => $payment->amount,
                        'type' => 'service_sale',
                        'status' => 'completed',
                        'details' => 'استكمال لبيع خدمة' . $title,
                    ]);
                } else {
                    // If payment failed, delete the contract
                    $contract->delete();
                }

                return response()->json(['chat' => $chat, 'contract' => $contract, 'success' => true, 'message' => 'Payment processed and contract created successfully.']);
            }
        }
    }
    // Method for admin to approve manual payments
    public function approveManualPayment(Request $request, $id)
    {
        $payment = payments::find($id);
        if ($payment && $payment->manual_payment && $payment->status === 'pending_admin_approval') {
            $payment->status = 'completed';

            $payment->save();

            // Update the contract status
            $contract = Contract::find($payment->contract_id);
            if ($contract) {
                $contract->status = 'in_progress';
                $contract->save();
            }

            // Update the balance
            $balance = balances::where('user_id', $contract->buyer_id)->first();
            if ($balance) {
                $balance->pending_balance -= $payment->amount;
                $balance->save();
            }

            return response()->json(['success' => true, 'message' => 'Manual payment approved successfully.']);
        }

        return response()->json(['success' => false, 'message' => 'Invalid payment or already approved.'], 400);
    }
}
