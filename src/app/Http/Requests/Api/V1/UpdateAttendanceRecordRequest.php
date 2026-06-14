<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAttendanceRecordRequest extends FormRequest
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
            'work_date' => [
                'required',
                'date',
                Rule::unique('attendance_records')
                    ->ignore($this->route('attendance_record'))
                    ->where('user_id', $this->user_id)
            ],
            'clock_in' => [
                'required',
                'date_format:H:i:s'
            ],
            'clock_out' => [
                'nullable',
                'date_format:H:i:s',
                'after:clock_in'
            ],
            'comment' => [
                'nullable',
                'string',
                'max:255'
            ],
        ];
    }

    public function messages()
    {
        return [
            'work_date.required' => '勤怠日は必須です。',
            'work_date.date' => '勤怠日はYYYY-MM-DD形式で入力してください。',
            'work_date.unique' => 'この日の勤怠は既に登録されています。',
            'clock_in.required' => '出勤時刻は必須です。',
            'clock_in.date_format' => '出勤時刻はHH:MM:SS形式で入力してください。',
            'clock_out.date_format' => '退勤時刻はHH:MM:SS形式で入力してください。',
            'clock_out.after' => '退勤時刻は出勤時刻より後で入力してください。',
            'comment.max' => '備考は255文字以内で入力してください。',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'user_id' => $this->route('attendance_record')->user_id,
        ]);
    }
}
