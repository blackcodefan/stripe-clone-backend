<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Appointment extends JsonResource
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
            'name' => $this->name,
            'object' => new \App\Http\Resources\CustomerObject($this->whenLoaded('object')),
            'status' => new \App\Http\Resources\Status($this->status),
            'email' => $this->email,
            'note' => $this->note,
            'type' => [
                'id' => $this->type,
                'name' => ($this->type == 1) ? __('global.out') : __('global.in') // 1 = NAAR BUITEN ---- 2 = NAAR BINNEN
            ],
            '_appointment_at' => $this->appointment_at->format('d-m-Y H:i'),
            'appointment_at' => $this->appointment_at->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at->format('d-m-Y H:i'),
            'updated_at' => $this->updated_at->format('d-m-Y H:i'),
            'notes' => \App\Http\Resources\Note::collection($this->notes)
        ];
    }
}
