<?php

namespace App\Mail\Prospect\Created;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Customer extends Mailable
{
    use Queueable, SerializesModels;

    protected $prospect;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($prospect)
    {
        $this->prospect = $prospect;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        // set dynamic config vars
        config([
            'app.name' => $this->prospect->company->name,
            'app.url' => $this->prospect->company->website,
        ]);

        return $this->markdown('emails.prospect.customer', [
            'prospect' => $this->prospect
        ])
            ->from($this->prospect->company->email, $this->prospect->company->name)
            ->subject('Bedankt voor uw inschrijving');
    }
}
