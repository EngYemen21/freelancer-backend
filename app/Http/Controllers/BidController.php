<?php

namespace App\Http\Controllers;

use App\Models\Bid;
use App\Events\BidCreated;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BidController extends Controller
{
    //
    public function index()
    {
        $bid = Bid::with('project','freelancer')->get();
        return response()->json($bid);
    }
    public function show($id){
        $Bides=Bid::where('project_id',$id)->with('freelancer','project')->get();
        if(!$Bides)
        {
            return response()->json(['Error'=>'No Bids Now' ]);
        }
        return response()->json($Bides);

    }
    public function store(Request $request, Project $project ,$id)
    {
       $validatedData= $request->validate([
            'amount' => 'required|numeric',
            'description' => 'required|string',
        ]);
        $user=auth()->user();
        $bid = new Bid();
        $bid->freelancer_id=$user->id;
        $bid->bid_amount = $validatedData['amount'];
        $bid->bid_description = $validatedData['description'];
        $bid->project_id = $id;
        $bid->dateTime=Carbon::now();
        if( $bid->save())
        {
            return response()->json(['data'=>$bid ,'message' => 'تم تقديم العرض بنجاح'], 201);

        }
        return response()->json(['error'=> '  حدث خطأ عند حفظ بيانات ']);

    }


    public function update(Request $request, $id)
    {
        $bid = Bid::findOrFail($id);
        $bid->update($request->all());
        return response()->json(['bid' => $bid]);
    }

    public function destroy($id)
    {
        $bid = Bid::findOrFail($id);
        $bid->delete();
        return response()->json(null, 204);
    }
}
