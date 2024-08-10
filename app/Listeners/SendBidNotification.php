<?php

namespace App\Listeners;

use App\Events\BidCreated;
use App\Models\NotificatonProject;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendBidNotification
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
    public function handle(BidCreated $event)
    {
        $bid = $event->bid;
        $project = $bid->project;
        $client = $project->client;

        NotificatonProject::create([
            'user_id' => $client->id,
            'type' => 'bid_created',

            'data' => json_encode([
                'project_id' => $project->id,
                'project_title' => $project->title,
                'freelancer_name' => $bid->freelancer->username,
                'bid_amount' => $bid->bid_amount,
            ]),
            'read' => false,
        ]);
        // php artisan make:listener StoreEventLog --event=
    }
}
