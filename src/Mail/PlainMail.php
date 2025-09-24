<?php

namespace Tv2regionerne\StatamicEvents\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PlainMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public array $config = []) {}

    public function content(): Content
    {
        return new Content(
            view: 'statamic-events::mail.html',
            text: 'statamic-events::mail.text'
        );
    }

    public function envelope(): Envelope
    {
        $fromParam = null;
        if ($from = $config['from'] ?? []) {
            if ($from['email'] ?? false) {
                $fromParam = new Address($from['email'], $from['name'] ?? '');
            }
        }

        return new Envelope(
            from: $fromParam,
            subject: $this->config['subject'] ?? __('No subject provided'),
        );
    }
}
