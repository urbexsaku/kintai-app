<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceBreakResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'break_in' => optional($this->start_at)->format('H:i:s'),
            'break_out' => optional($this->end_at)->format('H:i:s'),
        ];
    }
}
