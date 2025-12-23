<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Models\Course;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use App\Models\Attendance;
use App\Models\AttendanceOtp;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use App\Models\Schedule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\AbsentStudentVerificationMail;

class TeacherController extends Controller
{
    /* ================= DASHBOARD ================= */
    public function dashboard()
    {
        $teacher = Auth::user();
        $courses = $teacher->teachingCourses()->get();

        return Inertia::render('Teacher/Dashboard', [
            'courses' => $courses,
        ]);
    }

    /* ================= COURSES ================= */
    public function showCourse(Course $course)
    {
        abort_if($course->teacher_id !== Auth::id(), 403);

        $course->load('students', 'schedules');

        $enrolledStudentIds = $course->students->pluck('id');

        $unenrolledStudents = User::where('role', UserRole::STUDENT)
            ->whereNotIn('id', $enrolledStudentIds)
            ->orderBy('name')
            ->get();

        return Inertia::render('Teacher/ShowCourse', [
            'course' => $course,
            'unenrolledStudents' => $unenrolledStudents,
        ]);
    }
     public function enrollStudent(Request $request, Course $course)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id',
        ]);

        $course->students()->attach($request->student_id);

        return back()->with('success', 'تم تسجيل الطالب بنجاح');
    }
    public function storeSchedule(Request $request, Course $course)
    {
        abort_if($course->teacher_id !== Auth::id(), 403);

        $request->validate([
            'day_of_week' => ['required'],
            'start_time'  => ['required'],
            'end_time'    => ['required', 'after:start_time'],
        ]);

        $course->schedules()->create($request->all());

        return back()->with('success', 'Schedule added successfully.');
    }

    /* ================= ATTENDANCE SESSION ================= */
    public function startAttendanceSession(Course $course)
    {
        abort_if($course->teacher_id !== Auth::id(), 403);

        $todayName = Carbon::now()->format('l');
        $schedule = $course->schedules()
            ->where('day_of_week', $todayName)
            ->first();

        if (!$schedule) {
            return back()->with('error', 'No schedule today.');
        }

        foreach ($course->students as $student) {
            Attendance::firstOrCreate([
                'student_id'      => $student->id,
                'schedule_id'     => $schedule->id,
                'attendance_date' => today(),
            ], [
                'is_present' => false,
            ]);
        }

        $todaysAttendance = Attendance::where('schedule_id', $schedule->id)
            ->whereDate('attendance_date', today())
            ->with('student')
            ->get();

        return Inertia::render('Teacher/Attendance/Session', [
            'course' => $course,
            'schedule' => $schedule,
            'todaysAttendance' => $todaysAttendance,
        ]);
    }

    /* ================= FACE RECOGNITION ================= */
    public function markAttendance(Request $request)
    {
        $request->validate([
            'image'       => 'required|image',
            'schedule_id' => 'required|exists:schedules,id',
        ]);

        try {
            $response = Http::attach(
                'image',
                file_get_contents($request->file('image')),
                'frame.jpg'
            )->post('http://127.0.0.1:5000/recognize-face');

            if ($response->successful()) {
                $studentId = $response->json('student_id');

                Attendance::where('schedule_id', $request->schedule_id)
                    ->where('student_id', $studentId)
                    ->whereDate('attendance_date', today())
                    ->update([
                        'is_present'  => true,
                        'attended_at' => now(),
                    ]);

                return response()->json(['status' => 'success']);
            }

        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }

        return response()->json(['status' => 'not_recognized'], 404);
    }

    /* ================= END SESSION + SEND OTP ================= */
    public function endAttendanceSession(Course $course, Request $request)
{
    $scheduleId = $request->input('schedule_id');

    $absentAttendances = Attendance::where('schedule_id', $scheduleId)
        ->whereDate('attendance_date', today())
        ->where('is_present', false)
        ->with('student')
        ->get();

    foreach ($absentAttendances as $attendance) {
        $student = $attendance->student;

        if (!$student || !$student->email) {
            continue;
        }

        // 🔐 توليد OTP
        $otp = (string) random_int(100000, 999999);

        // 🗄️ تخزين HASH فقط
        AttendanceOtp::create([
            'student_id'    => $student->id,
            'attendance_id' => $attendance->id,
            'otp_hash'      => Hash::make($otp),
            'expires_at'    => now()->addMinutes(10),
        ]);

        // 📧 إرسال الإيميل
        Mail::to($student->email)
            ->send(new AbsentStudentVerificationMail($otp, $course->name));
    }

    // تحويل المعلم مباشرة لواجهة OTP
    return redirect()
        ->route('teacher.attendance.otp.show', $course->id)
        ->with('success', 'تم إرسال رموز التحقق للطلبة الغائبين');
}

    /* ================= OTP VIEW (TEACHER) ================= */
    public function showOtpVerification(Course $course)
    {
        $unverifiedAttendances = Attendance::whereHas('schedule', function ($q) use ($course) {
            $q->where('course_id', $course->id);
        })
        ->whereDate('attendance_date', today())
        ->where('is_present', false)
        ->with('student')
        ->get();

        return Inertia::render('Teacher/Attendance/OtpVerification', [
            'course' => $course,
            'unverifiedAttendances' => $unverifiedAttendances,
        ]);
    }

    /* ================= VERIFY OTP (TEACHER ONLY) ================= */
    public function verifyOtpManually(Request $request)
{
    $request->validate([
        'attendance_id' => 'required|exists:attendances,id',
        'otp' => 'required|digits:6',
    ]);

    $otpRecord = AttendanceOtp::where('attendance_id', $request->attendance_id)
        ->whereNull('used_at')
        ->where('expires_at', '>', now())
        ->first();

    if (!$otpRecord || !Hash::check($request->otp, $otpRecord->otp_hash)) {
        return back()->withErrors([
            'otp' => 'OTP غير صحيح أو منتهي',
        ]);
    }

    // ✅ جلب الحضور بشكل صريح
    $attendance = Attendance::find($otpRecord->attendance_id);

    if (!$attendance) {
        return back()->withErrors([
            'otp' => 'سجل الحضور غير موجود',
        ]);
    }

    if ($attendance->is_present) {
        return back()->withErrors([
            'otp' => 'تم تأكيد حضور هذا الطالب مسبقًا',
        ]);
    }

    // ✅ تسجيل الحضور
    $attendance->update([
        'is_present'  => true,
        'attended_at' => now(),
    ]);

    // 🔒 تعطيل OTP
    $otpRecord->update([
        'used_at' => now(),
    ]);

    return back()->with('success', 'تم تأكيد الحضور بنجاح ✅');
}

}
