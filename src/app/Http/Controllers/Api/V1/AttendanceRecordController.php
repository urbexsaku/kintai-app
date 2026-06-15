<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\IndexAttendanceRecordRequest;
use App\Http\Requests\Api\V1\StoreAttendanceRecordRequest;
use App\Http\Requests\Api\V1\UpdateAttendanceRecordRequest;
use App\Http\Resources\AttendanceRecordResource;
use App\Models\AttendanceRecord;

class AttendanceRecordController extends Controller
{
    /**
     * 勤怠一覧を取得する
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
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
                fn ($q) => $q->where(
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
            ->when(
                $request->month,
                function ($q) use ($request) {
                    [$year, $month] = explode('-', $request->month);

                    $q->whereYear('work_date', $year)
                        ->whereMonth('work_date', $month);
                }
            )
            ->latest('work_date')
            ->paginate($perPage);

        return AttendanceRecordResource::collection(
            $records
        );
    }

    /**
     * 勤怠を登録する
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreAttendanceRecordRequest $request)
    {
        $attendanceRecord = $request->user()
            ->attendanceRecords()
            ->create(
                $request->validated()
            );

        $attendanceRecord->load([
            'user',
            'breakRecords',
        ]);

        return (new AttendanceRecordResource(
            $attendanceRecord
        ))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * 勤怠詳細を取得する
     *
     * @return AttendanceRecordResource
     */
    public function show(AttendanceRecord $attendanceRecord)
    {
        $attendanceRecord->load([
            'user',
            'breakRecords',
            'attendanceCorrectRequests',
        ]);

        return new AttendanceRecordResource(
            $attendanceRecord
        );
    }

    /**
     * 勤怠情報を更新する
     *
     * @return AttendanceRecordResource
     */
    public function update(UpdateAttendanceRecordRequest $request, AttendanceRecord $attendanceRecord)
    {
        $this->authorize('update', $attendanceRecord);

        $attendanceRecord->update(
            $request->validated()
        );

        $attendanceRecord->load([
            'user',
            'breakRecords',
        ]);

        return new AttendanceRecordResource(
            $attendanceRecord
        );
    }

    /**
     * 勤怠情報を削除する
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function destroy(AttendanceRecord $attendanceRecord)
    {
        $this->authorize('delete', $attendanceRecord);

        $attendanceRecord->delete();

        return response()->noContent();
    }
}
