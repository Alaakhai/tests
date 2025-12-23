<?php

namespace Database\Factories;

use App\Models\AttendanceOtp;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class AttendanceOtpFactory extends Factory
{
    protected $model = AttendanceOtp::class;

    public function definition(): array
    {
        return [
            'attendance_id' => Attendance::factory(),
            'student_id'    => User::factory(),
            'otp_hash'      => bcrypt('123456'),
            'expires_at'    => Carbon::now()->addMinutes(5),
            'used_at'       => null,
        ];
    }
}
