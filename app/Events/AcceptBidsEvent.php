<?php

namespace App\Events;

use App\Models\AcceptedBid;
use App\Models\Notificationes;
use App\Models\NotifcationProject;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class AcceptBidsEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public $CreateNotificationProjectsAcceptBidsEvent;
//    public $message="لقد قبل العرض الخاص بك حول الخدمة'";

    public function __construct( Notificationes $CreateNotificationProjectsAcceptBidsEvent)
    {
        $this->CreateNotificationProjectsAcceptBidsEvent = $CreateNotificationProjectsAcceptBidsEvent;
        //  $this->message;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn()
    {
        return new PrivateChannel('private-AcceptBidsEvent.'. $this->CreateNotificationProjectsAcceptBidsEvent->user_id);
    }

}
