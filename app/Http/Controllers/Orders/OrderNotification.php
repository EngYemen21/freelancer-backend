<?php

namespace App\Http\Controllers\Orders;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class OrderNotification extends Controller
{
    //
    public function index()
    {
        $notifications = Auth::user()->notifications()->where('is_read', 0)->get();



        return response()->json($notifications);
    }

    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->is_read = 1;
        $notification->save();

        return response()->json(['message' => 'تم وضع الإشعار كمقروء']);
    }
}
