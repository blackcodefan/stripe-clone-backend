<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Prospect extends JsonResource
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
            'initials' => $this->initials,
            'full_name' => $this->full_name,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'email' => $this->email,
            'phone' => $this->phone,
            'street' => $this->street,
            'number' => $this->number,
            'zipcode' => $this->zipcode,
            'city' => $this->city,
            'brand' => $this->brand,
            'type' => $this->type,
            'license_plate' => $this->license_plate,
            'width' => $this->width,
            'length' => $this->length,
            'object' => new \App\Http\Resources\CustomerObject($this->object),
            'object_type_id' => $this->object_type_id,
            'object_type' => new \App\Http\Resources\ObjectType($this->object_type),
            'created_at' => $this->created_at->format('d-m-Y'),
            'delivery_at' => $this->delivery_at->format('d-m-Y'),
            'status' => new \App\Http\Resources\Status($this->status),
            'notes' => \App\Http\Resources\Note::collection($this->notes)
        ];
    }
}
