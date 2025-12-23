<?php

namespace Tests\Feature\Teacher;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceOtp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class AttendanceOtpTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function teacher_can_verify_otp_successfully()
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'student_id' => $student->id,
            'is_present' => false,
        ]);

        AttendanceOtp::create([
            'student_id'    => $student->id,
            'attendance_id' => $attendance->id,
            'otp_hash'      => Hash::make('123456'),
            'expires_at'    => now()->addMinutes(5),
        ]);

        $response = $this
            ->actingAs($teacher)
            ->post(route('teacher.attendance.otp.verify'), [
                'attendance_id' => $attendance->id,
                'otp' => '123456',
            ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'is_present' => true,
        ]);
    }

    /** @test */
    public function otp_cannot_be_used_twice()
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'student_id' => $student->id,
        ]);

        AttendanceOtp::create([
            'student_id'    => $student->id,
            'attendance_id' => $attendance->id,
            'otp_hash'      => Hash::make('654321'),
            'expires_at'    => now()->addMinutes(5),
            'used_at'       => now(),
        ]);

        $response = $this
            ->actingAs($teacher)
            ->post(route('teacher.attendance.otp.verify'), [
                'attendance_id' => $attendance->id,
                'otp' => '654321',
            ]);

        $response->assertSessionHasErrors('otp');
    }

    /** @test */
    public function expired_otp_is_rejected()
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'student_id' => $student->id,
        ]);

        AttendanceOtp::create([
            'student_id'    => $student->id,
            'attendance_id' => $attendance->id,
            'otp_hash'      => Hash::make('999999'),
            'expires_at'    => now()->subMinute(),
        ]);

        $response = $this
            ->actingAs($teacher)
            ->post(route('teacher.attendance.otp.verify'), [
                'attendance_id' => $attendance->id,
                'otp' => '999999',
            ]);

        $response->assertSessionHasErrors('otp');
    }
}

