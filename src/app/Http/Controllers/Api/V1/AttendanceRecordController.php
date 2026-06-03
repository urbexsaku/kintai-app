<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\IndexAttendanceRecordRequest;
use App\Http\Requests\Api\V1\UpdateAttendanceRecordRequest;
use App\Http\Resources\AttendanceRecordResource;
use App\Models\AttendanceRecord;
use Illuminate\Http\Request;

class AttendanceRecordController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(IndexAttendanceRecordRequest $request)
    {
        // ページネーション　デフォルト 20、最大 100
        $perPage = min(
            $request->per_page ?? 20,
            100
        );

        $records = AttendanceRecord::query()
            ->with(['user', 'breakRecords'])
            ->when(
                $request->user_id,
                fn($q) => $q->where(
                    'user_id',
                    $request->user_id
                )
            )
            ->when(
                $request->date,
                fn ($q) => $q->where(
                    'work_date',
                    $request->date
                )
            )
            ->latest('work_date')
            ->paginate($perPage);
        
        return AttendanceRecordResource::collection(
            $records
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(AttendanceRecord $attendanceRecord)
    {
        $atttendanceRecord->load([
            'user',
            'breakRecords',
            'attendanceCorrectRequests',
        ]);

        return new AttendanceRecordResource(
            $attendanceRecord
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAttendanceRecordRequest $request, AttendanceRecord $attendanceRecord)
    {
        $this->authorize('update', $attendanceRecord);

        $attendanceRecord->update(
            $request->validated()
        );

        return new AttendanceRecordResource(
            $attendanceRecord
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(AttendanceRecord $attendanceRecord)
    {
        $this->authorize('delete', $attendanceRecord);

        $attendanceRecord->delete();

        return response()->noContent();
    }
}
