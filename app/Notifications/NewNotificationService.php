<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Pusher\Pusher;

class NewNotificationService extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    // protected $userId;
    protected $message;
    protected $userID;
    protected $serviceID;
    // protected  $serviceId;


    public function __construct($message , $userID,$serviceID)
    {
        //
        $this->message = $message;
        $this->userID=$userID;
        $this->$serviceID=$serviceID;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    // public function toDatabase($notifiable)
    // {
    //     $notificationData = [
    //         'message' => $this->message,
    //         'service_id' => $this->serviceID,
    //         'user_id' => $this->userID,
    //         'is_read' => false
    //     ];

    //     return $notificationData;
    // }
    // public function broadcastOn()
    // {
    //     return ['my-channel'];
    // }

    // public function broadcastAs()
    // {
    //     return 'my-event';
    // }

    // public function toPusher($notifiable)
    // {
    //     $pusher = new Pusher(env('PUSHER_APP_KEY'), env('PUSHER_APP_SECRET'), env('PUSHER_APP_ID'));

    //     $event = 'notifications'; // Use a single event for different notification types
    //     $channel = "private-notifications";
    // $pusher->trigger($channel,$event,[
    //     'message' => 'hello',

    // ]);
    // }

        // $pusher->trigger($event, $channel, [

        //     'message' => $this->message,

        // ]);

    /**
     * Get the mail representation of the notification.
     */
    // public function toMail(object $notifiable): MailMessage
    // {
    //     return (new MailMessage)
    //                 ->line('The introduction to the notification.')
    //                 ->action('Notification Action', url('/'))
    //                 ->line('Thank you for using our application!');
    // }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
         return [
            'message' => $this->message,
            'service_id' => $this->serviceID,
            'user_id' => $this->userID,
            'is_read' => false
        ];
    }
}
