<?php

namespace App\Listeners;

use Log;
use App\Events\EventService;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ListenerEventService
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
    public function handle(EventService $event)
    {
        // Log the event or perform any other necessary actions
        // \Log::info('Received MyEvent with message:', ['message' => $event->message]);
        Log::channel('my-channel')->info('Received MyEvent with message:', ['message' => $event->message]);
    }
}
