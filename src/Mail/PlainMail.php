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

    public array $config = [];

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject($this->config['subject'])
            ->text('statamic-events::mail.text', [
                'text' => $this->config['plain'],
            ])
            ->view('statamic-events::mail.html', [
                'html' => $this->config['html'],
            ]);
    }

    public function content(): Content
    {
        return new Content(
            view: 'statamic-events::mail.html',
            text: 'statamic-events::mail.text'
        );
    }

    public function envelope(): Envelope
    {
        $from = null;
        if ($from = $config['from'] ?? []) {
            if ($from['email'] ?? false) {
                $from = new Address($from['email'], $from['name'] ?? '');
            }
        }

        return new Envelope(
            from: $from,
            subject: $this->config['subject'] ?? __('No subject provided'),
        );
    }
}
