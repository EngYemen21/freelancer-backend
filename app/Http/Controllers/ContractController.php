<?php

namespace App\Http\Controllers;

use Pusher\Pusher;
use App\Models\User;
use App\Models\balances;
use App\Models\Contract;
use App\Models\Services;
use App\Events\EventService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Events\EventForApproveOrRejectOrder;

class ContractController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function auth(Request $request)
    {
        $pusher = new Pusher(
            config('broadcasting.connections.pusher.key'),
            config('broadcasting.connections.pusher.secret'),
            config('broadcasting.connections.pusher.app_id'),
            [
                'cluster' => config('broadcasting.connections.pusher.options.cluster'),
                'encrypted' => true,
            ]
        );

        $channel_name = $request->input('channel_name');
        $socket_id = $request->input('socket_id');

        $presence_data = [
            'user_id' => Auth::id(),
            'user_info' => [
                'name' => Auth::user()->name,

            ],
        ];

        $auth = $pusher->authorizeChannel($channel_name, $socket_id, Auth::id());

        return response($auth);
    }

    public function index()
    {
        $contract = Contract::with('service', 'client', 'freelancer')->get();
        return response()->json($contract);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }


    public function store(Request $request)
    {
        $getEndDate = Services::findOrFail($request->service_id);


        $contract = Contract::create([
            'service_id' => $request->service_id,
            'client_id' => $request->buyer_id,
            'freelancer_id' => $request->seller_id,
            'price' => $request->price,
            'start_date' => now(),
            'end_date' => now()->addDays($getEndDate->delivery_time),
            'status' => 'pending',
        ]);

        return response()->json($contract, 201);
    }


    public function completeContract($id)
    {
        $contract = Contract::find($id);

        if ($contract && $contract->status === 'in_progress') {
            $contract->status = 'completed';
            $contract->save();

            $buyerBalance = balances::where('user_id', $contract->buyer_id)->first();
            $sellerBalance = balances::where('user_id', $contract->seller_id)->first();

            if ($buyerBalance) {
                $buyerBalance->pending_balance -= $contract->price;
                $buyerBalance->save();
            }

            if ($sellerBalance) {
                $sellerBalance->pending_balance -= $contract->price;
                $sellerBalance->available_balance += $contract->price;
                $sellerBalance->earned_balance += $contract->price;
                $sellerBalance->save();
            }

            return response()->json(['success' => true, 'message' => 'Contract completed successfully.']);
        }

        return response()->json(['success' => false, 'message' => 'Invalid contract status.'], 400);
    }

    public function showContractService(Request $request, string $id, string $sellerID, string $buyerID)
    {



        $orders = Contract::where('client_id', $buyerID)
            ->where('freelancer_id', $sellerID)
            ->where('service_id', $id)
            ->first();


        return response()->json($orders);
    }


    public function update(Request $request, $id, Contract $contract)
    {
        // استلام المعلومات من الطلب
        $contractID = $request->input('contractID');
        $clientID = auth()->id();
        $freelancer_id = $request->input('freelancer_id');
        $service_id = $request->input('service_id');
        $status = $request->input('status');

        // التحقق من أن الطلب موجود وأن البائع أو المشتري هما من يحاولان التحديث
        $contract = Contract::where('id', $contractID)
            ->where(function ($query) use ($freelancer_id, $clientID) {
                $query->where('freelancer_id', $freelancer_id)
                    ->orWhere('client_id', $clientID);
            })
            ->firstOrFail();


        $contract->status = $status;
        if ($status === 'in_progress') {
            $message = " ارسل لك " . Auth::user()->firstName . "لبدء تنفيذ الخدمة";
            event(new EventService($message, $request->freelancer_id, $contract));
            $contract->save();
        } elseif ($status === 'completed') {
            $message = " ارسل لك " . Auth::user()->firstName . "لتأكيد أستلام الخدمة ";

            event(new EventService($message, $request->freelancer_id, $contract));

            $contract->save();
            if ($contract->save()) {


                $contract = Contract::where('payment_id', $request->payment_id)->where('service_id', $service_id)->first();
                // $amount = $contract->price;
                if ($contract) {
                    $amount = $contract->price;
                } else {

                    $amount = null;
                }
                if ($amount <= 100000) {
                    $commission = $amount * 0.10;
                } else {
                    $commission = $amount * 0.05;
                }

                $freelancerAmount = $amount - $commission;
                $platformBalance = balances::where('user_id', 1)->first(); // 1: معرف المستخدم للمنصة


                if ($platformBalance) {

                    $platformBalance->total_earnings += $commission;
                    $platformBalance->save();
                } else {

                    $newBalance = new balances();
                    $newBalance->user_id = 1;
                    $newBalance->total_earnings += $commission;
                    $newBalance->total_balance = 0;
                    $newBalance->withdrawable_balance = 0;
                    $newBalance->pending_balance = 0;

                    $newBalance->save();
                }
            }
        } else {
            $message = " لقد قام " . Auth::user()->firstName . "بالغاء تنفيذ الخدمة معك";
            event(new EventService($message, $request->freelancer_id, $contract));
            $contract->save();
        }

        return response()->json($contract);
    }
    public function getServiceTypeSales(Request $request)
    {
        $servicesID = $request->input('serviceID');

        $serviceSales = Contract::select('service_id')
            // ->whereIn('service_id', $servicesID)
            ->where('status', ['completed'])
            ->groupBy('service_id')
            ->get();

        if (count($serviceSales) > 0) {
            return response()->json(count($serviceSales), 201);
        } else {
            return response()->json([], 201); // إرجاع مصفوفة فارغة إذا لم يتم العثور على أي نتائج
        }
    }

    public function getEventForApproveOrReject(Request $request)
    {

        $user = $request->freelancer_id;
        $contractID = $request->contractID;
        $contract = Contract::find($contractID);
        $status = $request->status;
        $contract->status = $status;
        // $freelancer_id = User::find($user);
        $contract->save();



        return response()->json(($contract));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::find($id);
        $contracts = Contract::with(['client', 'freelancer', 'service'])->where('freelancer_id', $user->id)->orWhere('client_id', $user->id)->get();

        if ($contracts->isEmpty()) {
            return response()->json(['error' => 'Contract not found'], 404);
        }
        // return response()->json(['$contracts'=>$contracts,'$user'=>$user]);
        return response()->json($contracts);
    }
    public function showContractOrder(Request $request, string $id)
    {
        // $user =User::Contract($id);
        $contracts = Contract::with(['client', 'freelancer', 'service'])->where('id',  $id)->get();

        if ($contracts->isEmpty()) {
            return response()->json(['error' => 'Contract not found'], 404);
        }

        return response()->json($contracts);
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
