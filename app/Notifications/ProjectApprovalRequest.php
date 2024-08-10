<?php

namespace App\Notifications;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ProjectApprovalRequest extends Notification
{

        use Queueable;

        protected $project;

        public function __construct(Project $project)
        {
            $this->project = $project;
        }

        public function via($notifiable)
        {
            return ['mail'];
        }

        public function toMail($notifiable)
        {
            return (new MailMessage)
                        ->subject('New Project Approval Request')
                        ->line('A new project requires approval:')
                        ->line('Project Title: ' . $this->project->title)
                        ->line('Project Description: ' . $this->project->description)
                        ->action('View Project', url('/projects/' . $this->project->id));
        }

        public function toArray($notifiable)
        {
            return [
                'project_id' => $this->project->id,
                'project_title' => $this->project->title,
                'message' => 'New project approval request',
            ];
        }
}
