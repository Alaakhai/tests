<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\AttendanceOtp;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AttendanceOtpTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function attendance_otp_can_be_created()
    {
        $student = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'student_id' => $student->id,
        ]);

        $otp = AttendanceOtp::create([
            'student_id'    => $student->id,
            'attendance_id' => $attendance->id,
            'otp_hash'      => bcrypt('123456'),
            'expires_at'    => now()->addMinutes(10),
        ]);

        $this->assertDatabaseHas('attendance_otps', [
            'id' => $otp->id,
            'student_id' => $student->id,
            'attendance_id' => $attendance->id,
        ]);
    }

    /** @test */
    public function expires_at_is_casted_to_datetime()
    {
        $otp = AttendanceOtp::factory()->create([
            'expires_at' => now()->addMinutes(5),
        ]);

        $this->assertInstanceOf(Carbon::class, $otp->expires_at);
    }

    /** @test */
    public function used_at_can_be_null_or_datetime()
    {
        $otp = AttendanceOtp::factory()->create([
            'used_at' => null,
        ]);

        $this->assertNull($otp->used_at);

        $otp->update(['used_at' => now()]);

        $this->assertInstanceOf(Carbon::class, $otp->fresh()->used_at);
    }

    /** @test */
    public function attendance_otp_uses_correct_table()
    {
        $otp = new AttendanceOtp();

        $this->assertEquals('attendance_otps', $otp->getTable());
    }
}
