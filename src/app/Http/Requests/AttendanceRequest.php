<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'clock_in' => ['required'],
            'clock_out' => ['required'],
            'comment' => ['required'],
        ];
    }

    public function messages()
    {
        return [
            'clock_in.required' => '出勤時間を入力してください',
            'clock_out.required' => '退勤時間を入力してください',
            'comment.required' => '備考を記入してください',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $clockIn = $this->clock_in;
            $clockOut = $this->clock_out;

            // 出勤時刻が退勤時刻より後 & 退勤時刻が出勤時刻より前
            if ($clockIn && $clockOut && $clockIn >= $clockOut) {
                $validator->errors()->add(
                    'clock_in',
                    '出勤時間もしくは退勤時間が不適切な値です'
                );
            }

            // 休憩データがある場合データを返し、ない場合は空配列を返す
            foreach ($this->start_at ?? [] as $index => $start) {
                $end = $this->end_at[$index] ?? null;

                // 休憩開始も終了も空の場合は無視
                if (!$start && !$end) {
                    continue;
                }

                // 休憩開始が出勤時間より前 & 退勤時間より後
                if ($start && ($start < $clockIn || $start > $clockOut)) {
                    $validator->errors()->add(
                        "start_at.$index",
                        '休憩時間が不適切な値です',
                    );
                }

                // 休憩終了が退勤時間より後              
                if ($end && $end > $clockOut) {
                    $validator->errors()->add(
                        "end_at.$index",
                        '休憩時間もしくは退勤時間が不適切な値です',
                    );
                }

                // 休憩終了が休憩開始より前 (追加)
                if ($start && $end && $start >= $end) {
                    $validator->errors()->add(
                        "end_at.$index",
                        '休憩時間が不適切な値です',
                    );
                }
            }
        });
    }
}
