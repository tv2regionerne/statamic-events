<?php

namespace Tv2regionerne\StatamicEvents\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PlainMail extends Mailable
{
    use Queueable, SerializesModels;

    public $config = [
        'plain' => '',
        'html' => '',
        'subject' => '',
    ];

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($subject, $plain, $html)
    {
        $this->config['subject'] = $subject ?? __('No subject provided');
        $this->config['plain'] = $plain ?? '';
        $this->config['html'] = $html ?? '';
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
            ->html('statamic-events::mail.html', [
                'html' => $this->config['html'],
            ]);
    }
}
