<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Course;
use App\Models\Schedule;

class StoreScheduleRequest extends FormRequest
{
    /**
     * تحديد إذا كان المستخدم مخوّل لإجراء الطلب.
     */
    public function authorize(): bool
    {
        // نجعلها true لأن الأدمن هو من يستخدم هذه الواجهة
        return true;
    }

    /**
     * قواعد التحقق الأساسية.
     */
    public function rules(): array
    {
        return [
            'course_id' => ['required', 'integer', Rule::exists('courses', 'id')],
            'day_of_week' => ['required', 'string', 'max:10'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'note' => ['nullable', 'string'],
        ];
    }

    /**
     * التحقق الإضافي بعد الفاليديشن الأساسي.
     * يمنع التعارض في أوقات المحاضرات للمدرس أو الطلاب.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $courseId = $this->input('course_id');
            $day = $this->input('day_of_week');
            $start = $this->input('start_time');
            $end = $this->input('end_time');

            // التحقق الأساسي
            if (! $courseId || ! $day || ! $start || ! $end) {
                return;
            }

            // الحصول على الدورة ومعها المدرس والطلاب
            $course = Course::with('students')->find($courseId);

            if (! $course) {
                $validator->errors()->add('course_id', 'The selected course was not found.');
                return;
            }

            // تحقق أن وقت البداية أصغر من النهاية
            if ($start >= $end) {
                $validator->errors()->add('start_time', 'Start time must be before end time.');
            }

            // 1️⃣ فحص تعارض المعلم
            $teacherId = $course->teacher_id;
            if ($teacherId && Schedule::hasOverlapForTeacher($teacherId, $day, $start, $end)) {
                $validator->errors()->add('start_time', 'This teacher already has a class at that time.');
            }

            // 2️⃣ فحص تعارض الطلاب المسجلين
            foreach ($course->students as $student) {
                if (Schedule::hasOverlapForStudent($student->id, $day, $start, $end)) {
                    $validator->errors()->add('start_time', 'One or more students already have a class at that time.');
                    break; // نوقف عند أول تعارض
                }
            }
        });
    }
}
