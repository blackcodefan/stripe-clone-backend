<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WebhookReceived
{
    use Dispatchable, SerializesModels;

    /**
     * The webhook payload.
     *
     * @var array
     */
    public $payload;
    public $company_id;

    /**
     * Create a new event instance.
     *
     * @param  array  $payload
     * @return void
     */
    public function __construct(array $payload, $company_id)
    {
        $this->payload = $payload;
        $this->company_id = $company_id;
    }
}
