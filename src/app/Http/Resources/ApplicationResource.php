<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'requested_clock_in' => optional($this->requested_clock_in)->format('H:i:s'),
            'requested_clock_out' => optional($this->requested_clock_out)->format('H:i:s'),
            'comment' => $this->comment,
        ];
    }
}
