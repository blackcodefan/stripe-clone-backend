<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Customer extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'stripe_id' => $this->stripe_id,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'full_name' => $this->full_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'street' => $this->street,
            'number' => $this->number,
            'zipcode' => $this->zipcode,
            'city' => $this->city,
            'company' => $this->company,
            'objects' => \App\Http\Resources\CustomerObject::collection($this->whenLoaded('objects')),
        ];
    }
}
