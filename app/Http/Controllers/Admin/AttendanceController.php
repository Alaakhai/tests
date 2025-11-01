<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Attendance;
use App\Models\User;
use App\Models\Course;
use App\Models\Schedule;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'date'        => $request->input('date'),
            'student_id'  => $request->input('student_id'),
            'course_id'   => $request->input('course_id'),
            'schedule_id' => $request->input('schedule_id'),
            'is_present'  => $request->input('is_present'),
        ];

        $query = Attendance::query()
            ->with(['student', 'schedule'])
            ->latest('attendance_date');

        if (!empty($filters['date'])) {
            $query->whereDate('attendance_date', $filters['date']);
        }

        if (!empty($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }

        if (!empty($filters['schedule_id'])) {
            $query->where('schedule_id', $filters['schedule_id']);
        }

        if (!empty($filters['course_id'])) {
            $query->whereHas('schedule', function ($q) use ($filters) {
                $q->where('course_id', $filters['course_id']);
            });
        }

        if ($filters['is_present'] !== null && $filters['is_present'] !== '') {
            $query->where('is_present', (int) $filters['is_present']);
        }

        $records = $query->paginate(15)->withQueryString();

        $students  = User::where('role', 'student')->select('id', 'name')->get();
        $courses   = Course::select('id', 'name')->get();

        // استخدام الأعمدة المتوفرة فعليًا في جدول schedules
        $schedules = Schedule::query()
            ->select('id', 'day_of_week', 'start_time', 'end_time')
            ->orderBy('id', 'desc')
            ->get();

        // ملاحظة: المسار هنا صغير "admin" ليتطابق مع مجلدك Pages/admin/Attendance/Index.vue
        return Inertia::render('admin/Attendance/Index', [
            'records'   => $records,
            'filters'   => $filters,
            'students'  => $students,
            'courses'   => $courses,
            'schedules' => $schedules,
        ]);
    }
}
