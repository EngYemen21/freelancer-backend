<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use App\Models\NotifcationProject;
use App\Models\CustomDatabaseNotification;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class BidAccepted extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    protected $bid;

    public function __construct($bid)
    {
        $this->bid = $bid;
    }
    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable)
    {
        return [
            'message' => 'Your bid has been accepted!',
            'bid_id' => $this->bid->id,
            'project_id' => $this->bid->project_id,
            'user_id'=>$this->bid->freelancer_id,
        ];
    }
    protected function createDatabaseNotification($notifiable, array $data)
    {
        return NotifcationProject::create([
            'user_id' =>$this->bid->freelancer_id,
            'message' => $data['message'],
            'bid_id' => $data['bid_id'],
            'project_id' => $data['project_id'],
        ]);
    }
}
