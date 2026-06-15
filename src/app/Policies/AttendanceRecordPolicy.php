<?php

namespace App\Policies;

use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AttendanceRecordPolicy
{
    use HandlesAuthorization;

    /**
     * 管理者の場合は全ての操作を許可する
     *
     * @param  string  $ability
     * @return bool|null
     */
    public function before(User $user, $ability)
    {
        if ($user->admin_status) {
            return true;
        }
    }

    /**
     * 勤怠情報を更新できるか判定する
     *
     * @return bool
     */
    public function update(User $user, AttendanceRecord $attendanceRecord)
    {
        return $user->id === $attendanceRecord->user_id;
    }

    /**
     * 勤怠情報を削除できるか判定する
     *
     * @return bool
     */
    public function delete(User $user, AttendanceRecord $attendanceRecord)
    {
        return $user->id === $attendanceRecord->user_id;
    }
}
