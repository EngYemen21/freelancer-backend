<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NotifcationProject;
use App\Models\Notificationes;

class NotificationAcceptBidController extends Controller
{
    //
    public function index(){
        $notificationProject=Notificationes::where('user_id',auth()->user()->id)->get();
        return response()->json([$notificationProject]);
    }
   
}
