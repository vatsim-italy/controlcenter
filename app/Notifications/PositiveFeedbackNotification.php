<?php

namespace App\Notifications;

use anlutro\LaravelSettings\Facade as Setting;
use App\Mail\PositiveFeedbackMail;
use App\Models\Endorsement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PositiveFeedbackNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $sender;
    private $feedback;

    /**
     * Create a new notification instance.
     *
     * @param  Endorsement  $endorsement
     */
    public function __construct($sender, $feedback)
    {
        $this->sender = $sender;
        $this->feedback = $feedback;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return PositiveFeedbackMail
     */
    public function toMail($notifiable)
    {

        if (! Setting::get('feedbackEnabled')) {
            return false;
        }

        $recipient = $this->feedback->submitter;

        return (new PositiveFeedbackMail($recipient, $this->sender))
            ->to($recipient->email, $recipient->first_name);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [];
    }
}
