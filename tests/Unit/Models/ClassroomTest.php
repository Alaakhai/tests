<?php

namespace Tests\Unit\Models;

use App\Models\Classroom;
use App\Models\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClassroomTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function classroom_can_be_created()
    {
        $classroom = Classroom::create([
            'name' => 'Lab-1',
            'capacity' => 30,
        ]);

        $this->assertDatabaseHas('classrooms', [
            'name' => 'Lab-1',
            'capacity' => 30,
        ]);
    }

    /** @test */
    public function classroom_has_many_schedules()
    {
        $classroom = Classroom::factory()
            ->has(Schedule::factory()->count(3))
            ->create();

        $this->assertCount(3, $classroom->schedules);
    }
}
