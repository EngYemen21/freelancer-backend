<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Disputes;
use Illuminate\Http\Request;

class DisputeController extends Controller
{
    //
    public function index()
    {
        $disputes = Disputes::with(['client', 'freelancer'])->get();
        return response()->json($disputes);
    }
    public function show(Disputes $dispute)
    {
        return response()->json($dispute->load(['client', 'freelancer']));
    }
    public function store(Request $request)
    {
        $request->validate([
            'issue' => 'required|string',
            'client_id' => 'required|integer',
            'freelancer_id' => 'required|integer',
        ]);

        $dispute = Disputes::create($request->all());

        return response()->json(['dispute' => $dispute], 201);
    }

    public function update(Request $request, $id)
    {
        $dispute = Disputes::findOrFail($id);

        $request->validate([
            'issue' => 'sometimes|required|string',
            // 'resolution' => 'sometimes|required|string',
            'status' => 'sometimes|required|string',
        ]);

        $dispute->update($request->all());

        return response()->json(['dispute' => $dispute], 200);
    }

    public function destroy($id)
    {
        Disputes::findOrFail($id)->delete();

        return response()->json(null, 204);
    }

    public function resolve(Request $request, $id)
    {
        $request->validate([
            'resolution' => 'required|string',
        ]);

        $dispute = Disputes::findOrFail($id);
        $dispute->issue = $request->resolution;
        $dispute->status = 'resolved';
        $dispute->save();
        if($request-> send_to=='client')
        {
             $client = User::find($dispute->client_id);
            //  $client->notify(new \App\Notifications\DisputeResolved($dispute));
        }
        else{
            $freelancer = User::find($dispute->freelancer_id);
            // $freelancer->notify(new \App\Notifications\DisputeResolved($dispute));
        }

        // إخطار العميل أو المستقل
        // $client->notify(new \App\Notifications\DisputeResolved($dispute));
        // $freelancer->notify(new \App\Notifications\DisputeResolved($dispute));

        return response()->json(['message' => 'Dispute resolved successfully'], 200);
    }

}
