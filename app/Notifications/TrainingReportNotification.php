<?php

namespace App\Notifications;

use App\Mail\TrainingMail;
use App\Models\Evaluation;
use App\Models\Training;
use App\Models\TrainingReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class TrainingReportNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $training;

    private $evaluation;

    /**
     * Create a new notification instance.
     *
     * @param  Evaluation  $evaluation  to reference
     * @param  string  $key
     */
    public function __construct(Training $training, Evaluation $evaluation)
    {
        $this->training = $training;
        $this->evaluation = $evaluation;
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
     * @return TrainingMail
     */
    public function toMail($notifiable)
    {
        $textLines = [
            'Your mentor ' . $this->evaluation->author->name . ' has written a new report for your training.',
        ];

        return (new TrainingMail('Training Report', $this->training, $textLines, null, route('training.show', $this->training->id), 'Read Report'))
            ->to($this->training->user->personalNotificationEmail, $this->training->user->name);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'training_id' => $this->training->id,
            'training_report_id' => $this->evaluation->id,
        ];
    }
}
