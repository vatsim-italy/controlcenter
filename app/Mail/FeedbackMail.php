<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FeedbackMail extends Mailable
{
    use Queueable, SerializesModels;

    private string $mailSubject;

    private array $textLines;

    private $actionUrl;

    private $actionText;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $mailSubject, array $textLines, ?string $actionUrl = null, ?string $actionText = null)
    {
        $this->mailSubject = $mailSubject;
        $this->textLines = $textLines;
        $this->actionUrl = $actionUrl;
        $this->actionText = $actionText;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->mailSubject)->markdown('mail.feedback', [
            'textLines' => $this->textLines,
            'actionUrl' => $this->actionUrl,
            'actionText' => $this->actionText,
        ]);
    }
}
