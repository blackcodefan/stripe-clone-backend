<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Iframe extends JsonResource
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
            'company' => $this->company,
            'locations' => \App\Http\Resources\Location::collection($this->locations)
        ];
    }
}
