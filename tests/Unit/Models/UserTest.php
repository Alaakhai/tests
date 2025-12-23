<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_be_created_with_student_role(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::STUDENT,
        ]);

        $this->assertDatabaseHas('users', [
            'id'   => $user->id,
            'role' => UserRole::STUDENT->value,
        ]);
    }

    public function test_user_can_be_created_with_teacher_role(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::TEACHER,
        ]);

        $this->assertEquals(UserRole::TEACHER, $user->role);
    }

    public function test_user_can_be_created_with_admin_role(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::ADMIN,
        ]);

        $this->assertEquals(UserRole::ADMIN, $user->role);
    }

    public function test_password_is_hashed(): void
    {
        $user = User::factory()->create([
            'password' => 'secret123',
        ]);

        $this->assertTrue(
            Hash::check('secret123', $user->password)
        );
    }

    public function test_user_has_email_and_name(): void
    {
        $user = User::factory()->create();

        $this->assertNotNull($user->email);
        $this->assertNotNull($user->name);
    }
}
