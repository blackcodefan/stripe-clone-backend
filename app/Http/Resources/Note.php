<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Note extends JsonResource
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
            'note' => $this->note,
            'user' => new \App\Http\Resources\User($this->user),
            'created_at' => $this->created_at,
            // '_created_at' => $this->created_at->diffForHumans(),
        ];
    }
}
