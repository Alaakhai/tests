<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = ['course_id', 'day_of_week', 'start_time', 'end_time'];

    // --- العلاقات ---

    /**
     * علاقة "المحاضرة تتبع لمادة واحدة"
     * A Schedule belongs to a Course.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * علاقة "المحاضرة لها سجلات حضور كثيرة"
     * A Schedule has many Attendances.
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }


/**??new edit */
    /**
     * هل يتداخل هذا الجدول (instance) زمنياً مع فترة معطاة؟
     * نستخدم المقارنة البسيطة: لا تداخل عندما ينتهي أحدهما قبل بداية الآخر.
     *
     * @param string $start  // 'HH:MM'
     * @param string $end    // 'HH:MM'
     * @return bool          // true إذا هناك تداخل
     */
    public function overlapsWith(string $start, string $end): bool
    {
        // تأكد من أن القيم متوافقة بصيغة H:i قبل الاستدعاء
        return ! ( $end <= $this->start_time || $start >= $this->end_time );
    }

    /**
     * فحص سريع: هل يوجد تداخل لسجل جديد/مُحدَّث عند مستوى الدورة (course) ونفس اليوم؟
     *
     * @param int $courseId
     * @param string $dayOfWeek
     * @param string $start  // 'HH:MM'
     * @param string $end    // 'HH:MM'
     * @param int|null $excludeId  // id لسجل يجب استبعاده (عند التحديث)
     * @return bool  // true إذا هناك تداخل مع أي سجل موجود
     */
    public static function hasOverlapForCourse(int $courseId, string $dayOfWeek, string $start, string $end, ?int $excludeId = null): bool
    {
        return self::where('course_id', $courseId)
            ->where('day_of_week', $dayOfWeek)
            ->when($excludeId, fn($q) => $q->where('id', '<>', $excludeId))
            ->where(function($q) use ($start, $end) {
                $q->whereBetween('start_time', [$start, $end])
                  ->orWhereBetween('end_time', [$start, $end])
                  ->orWhere(function($q2) use ($start, $end) {
                      $q2->where('start_time', '<=', $start)
                         ->where('end_time', '>=', $end);
                  });
            })
            ->exists();
    }

    /**
     * فحص تعارض على مستوى المعلم: هل لدى هذا المعلم (teacher_id) أي جدول يتداخل؟
     * يفيد عند إنشاء/تعديل جدول للتأكد أن المعلم لا يدرس شيئين متداخلين.
     *
     * @param int $teacherId
     * @param string $dayOfWeek
     * @param string $start
     * @param string $end
     * @param int|null $excludeId
     * @return bool
     */
    public static function hasOverlapForTeacher(int $teacherId, string $dayOfWeek, string $start, string $end, ?int $excludeId = null): bool
    {
        return self::whereHas('course', function($q) use ($teacherId) {
                $q->where('teacher_id', $teacherId);
            })
            ->where('day_of_week', $dayOfWeek)
            ->when($excludeId, fn($q) => $q->where('id', '<>', $excludeId))
            ->where(function($q) use ($start, $end) {
                $q->whereBetween('start_time', [$start, $end])
                  ->orWhereBetween('end_time', [$start, $end])
                  ->orWhere(function($q2) use ($start, $end) {
                      $q2->where('start_time', '<=', $start)
                         ->where('end_time', '>=', $end);
                  });
            })
            ->exists();
    }

    /**
     * فحص تعارض على مستوى الطالب: هل لدى هذا الطالب أي جدول يتداخل؟
     * يعتمد أن لديك علاقة many-to-many between Course and User باسم course_user.
     *
     * @param int $studentId
     * @param string $dayOfWeek
     * @param string $start
     * @param string $end
     * @param int|null $excludeId
     * @return bool
     */
    public static function hasOverlapForStudent(int $studentId, string $dayOfWeek, string $start, string $end, ?int $excludeId = null): bool
    {
        return self::whereHas('course.students', function($q) use ($studentId) {
                $q->where('user_id', $studentId);
            })
            ->where('day_of_week', $dayOfWeek)
            ->when($excludeId, fn($q) => $q->where('id', '<>', $excludeId))
            ->where(function($q) use ($start, $end) {
                $q->whereBetween('start_time', [$start, $end])
                  ->orWhereBetween('end_time', [$start, $end])
                  ->orWhere(function($q2) use ($start, $end) {
                      $q2->where('start_time', '<=', $start)
                         ->where('end_time', '>=', $end);
                  });
            })
            ->exists();
    }

}