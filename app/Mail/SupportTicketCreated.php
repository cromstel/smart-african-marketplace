<?php

namespace App\Mail;

use App\Models\SupportTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SupportTicketCreated extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public SupportTicket $ticket) {}

    public function build(): self
    {
        return $this->subject('New Support Ticket #'.$this->ticket->id)
            ->view('emails.support-ticket-created');
    }
}
