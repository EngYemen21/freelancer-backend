<?php

namespace App\Events;

use App\Models\User;
use App\Models\Order;
use BeyondCode\LaravelWebSockets\WebSockets\Channels\PrivateChannel as ChannelsPrivateChannel;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class EventService implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;


    public $message;
    public $userID;
    public $order;

    public function __construct($message , $userID,$order)
    {
        $this->message = $message;
        $this->userID = $userID;
        $this->order = $order;

    }

    public function broadcastOn()
    {
        return new PrivateChannel('private-events.'.$this->userID);
    }

  


}
