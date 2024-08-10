<?php

namespace App\Providers;

use App\Events\AcceptBidsEvent;
use App\Events\BidCreated;
use Illuminate\Support\Facades\Event;
use App\Listeners\SendBidNotification;
use App\Listeners\StoreAcceptBidsEvents;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        BidCreated::class => [
            SendBidNotification::class,
        ],
        AcceptBidsEvent::class => [
            StoreAcceptBidsEvents::class,
        ],
        'App\Events\MyEvent' => [
            'App\Listeners\ListenerEventService',
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
         parent::boot();
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
