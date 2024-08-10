<?php

namespace App\Listeners;

use App\Events\AcceptBidEvent;
use App\Events\AcceptBidsEvent;
use App\Models\NotifcationProject;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class StoreAcceptBidsEvents
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */

    public function handle(AcceptBidsEvent $event): void
    {
        //
        // NotifcationProject::create([
        //     'user_id' => $event->AcceptBidsEvent->freelancer_id,
        //     'project_id' =>$event->AcceptBidsEvent->project_id,
        //     'bid_id' =>$event->AcceptBidsEvent->bid_id ,
        //     'message'=>'لقد قبل العرض الخاص بك حول الخدمة',
        // ]);
    }
}
