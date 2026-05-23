<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PositiveFeedbackMail extends Mailable
{
    use Queueable, SerializesModels;

    private $mailSubject = 'Thank you for your feedback';

    private $user;

    private $sender;

    public function __construct(User $user, User $sender)
    {
        $this->user = $user;
        $this->sender = $sender;
    }

    public function build()
    {
        return $this->subject($this->mailSubject)->markdown('mail.positive_feedback', [
            'firstName' => $this->user->first_name,
            'sender' => $this->sender->first_name,
        ]);
    }
}
