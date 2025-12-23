<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceOtp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class AttendanceOtpController extends Controller
{
    public function show($courseId)
    {
        $unverifiedAttendances = Attendance::with(['student', 'otp'])
            ->where('is_present', false)
            ->whereHas('otp', function ($q) {
                $q->where('expires_at', '>', now());
            })
            ->get();

        return Inertia::render('Teacher/Attendance/OtpVerification', [
            'courseId' => $courseId,
            'unverifiedAttendances' => $unverifiedAttendances,
        ]);
    }

    public function verify(Request $request)
    {
        $request->validate([
            'attendance_id' => 'required|exists:attendances,id',
            'otp' => 'required|digits:6',
        ]);

        $attendance = Attendance::findOrFail($request->attendance_id);

        if ($attendance->is_present) {
            return back()->withErrors([
                'otp' => 'تم تأكيد حضور هذا الطالب مسبقًا',
            ]);
        }

        $otpRecord = AttendanceOtp::where('attendance_id', $attendance->id)
            ->where('expires_at', '>', now())
            ->first();

        if (!$otpRecord || !Hash::check($request->otp, $otpRecord->otp_hash)) {
            return back()->withErrors([
                'otp' => 'رمز التحقق غير صحيح أو منتهي الصلاحية',
            ]);
        }

        // ✅ تسجيل الحضور
        $attendance->update([
            'is_present' => true,
            'attended_at' => now(),
        ]);

        // 🔒 حذف OTP (One-Time)
        $otpRecord->delete();

        return back()->with('success', 'تم تأكيد حضور الطالب بنجاح ✅');
    }
}
