<?php

namespace App\Mail\Appointment\Created;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Owner extends Mailable
{
    use Queueable, SerializesModels;

    protected $appointment;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($appointment)
    {
        $this->appointment = $appointment;
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
            'app.name' => $this->appointment->object->customer->company->name,
            'app.url' => $this->appointment->object->customer->company->website,
        ]);

        return $this->markdown('emails.appointment.owner', [
            'appointment' => $this->appointment
        ])
            ->from($this->appointment->object->customer->company->email, $this->appointment->object->customer->company->name)
            ->subject('Er is een nieuwe afspraak geplaatst');
    }
}
