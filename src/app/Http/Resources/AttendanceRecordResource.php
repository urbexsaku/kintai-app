<?php

namespace App\Http\Resources;

use App\Http\Resources\ApplicationResource;
use App\Http\Resources\AttendanceBreakResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceRecordResource extends JsonResource
{
    /**
     * APIレスポンス用に勤怠情報を成型する
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        // show用
        if ($this->relationLoaded('attendanceCorrectRequests')) {
            return [
                'id' => $this->id,

                'user' => new UserResource(
                    $this->whenLoaded('user')
                ),

                'date' => optional($this->work_date)->format('Y-m-d'),
                'clock_in' => optional($this->clock_in)->format('H:i:s'),
                'clock_out' => optional($this->clock_out)->format('H:i:s'),

                'breaks' => AttendanceBreakResource::collection(
                    $this->whenLoaded('breakRecords')
                ),

                'applications' => ApplicationResource::collection(
                    $this->whenLoaded('attendanceCorrectRequests')
                ),

                'comment' => $this->comment,
            ];
        }


        // index用
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user_name' => optional($this->user)->name,
            'date' => optional($this->work_date)->format('Y-m-d'),
            'clock_in' => optional($this->clock_in)->format('H:i:s'),
            'clock_out' => optional($this->clock_out)->format('H:i:s'),
            'total_time' => $this->workTime,
            'total_break_time' => $this->totalBreak,
            'comment' => $this->comment,
        ];
    }
}
