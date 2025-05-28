<?php

namespace App\Notifications;

use anlutro\LaravelSettings\Facade as Setting;
use App\Mail\FeedbackMail;
use App\Mail\StaffNoticeMail;
use App\Models\Endorsement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class FeedbackNotificationUser extends Notification implements ShouldQueue
{
    use Queueable;

    private $user;
    private $feedback;

    /**
     * Create a new notification instance.
     *
     * @param  Endorsement  $endorsement
     */
    public function __construct($user, $feedback)
    {
        $this->user=$user;
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
     * @return EndorsementMail
     */
    public function toMail($notifiable)
    {

        if (! Setting::get('feedbackEnabled')) {
            return false;
        }

        $position = isset($this->feedback->referencePosition) ? $this->feedback->referencePosition->callsign : 'N/A';

        $textLines = [
            'You have received a new feedback',
            '___',
            '**Position**: ' . ($position ? $position : 'N/A'),
            '___',
            '**Feedback**',
            $this->feedback->feedback,

        ];

        $feedbackForward = Setting::get('feedbackForwardEmail');
        $id = $this->user->id;
        return (new FeedbackMail('Feedback received', $textLines, "https://training.vatita.net/user/$id)", "Show all"))
            ->to($this->user->email, $this->user->first_name);
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
