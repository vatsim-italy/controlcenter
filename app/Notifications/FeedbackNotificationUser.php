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
        $feedbackText = str_replace("\n", "\n\n", $this->feedback->feedback); // Ensure paragraphs are spaced

        $textLines = [
            'You have received new feedback regarding your controlling:',
            '**Position:** ' . $position,
            '**Feedback:**',
            '> ' . $feedbackText, // Blockquote for better readability
        ];

        $id = $this->user->id;
        return (new FeedbackMail('Feedback received'))
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
