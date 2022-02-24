<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomerObject extends JsonResource
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
            'customer' => new \App\Http\Resources\Customer($this->whenLoaded('customer')),
            'license_plate' => $this->license_plate,
            'location_id' => $this->location_id,
            'spot' => $this->spot,
            'brand' => $this->brand,
            'type' => $this->type,
            'width' => $this->width,
            'length' => $this->length,
            'customer_id' => $this->customer_id,
            'object_id' => $this->object_id,
            'object_type_id' => $this->object_type_id,
            // 'notes' => \App\Http\Resources\Note::collection($this->notes),
            'object_type' => new \App\Http\Resources\ObjectType($this->object_type),
            'temp_name' => $this->whenLoaded('customer', function () {
                return $this->customer->full_name;
            }),
            'status' => [
                'id' => $this->getRawOriginal('status'),
                'name' => $this->status
            ],
            //'appointments' => \App\Http\Resources\Appointment::collection($this->appointments),
            'appointments' => \App\Http\Resources\Appointment::collection($this->whenLoaded('appointments')),
        ];
    }
}
