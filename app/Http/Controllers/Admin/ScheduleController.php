<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Schedule;
use App\Models\Course;
use App\Http\Requests\StoreScheduleRequest;
use App\Http\Requests\UpdateScheduleRequest;

class ScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $schedules = Schedule::with('course')
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->paginate(15)
            ->withQueryString();

        // ✅ يتوافق مع: resources/js/Pages/admin/Schedules/Index.vue
        return Inertia::render('admin/Schedules/Index', [
            'schedules' => $schedules,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $courses = Course::orderBy('name')->get(['id', 'name']);
        $days = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];

        // ✅ يتوافق مع: resources/js/Pages/admin/Schedules/Create.vue
        return Inertia::render('admin/Schedules/Create', [
            'courses' => $courses,
            'days' => $days,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreScheduleRequest $request)
    {
        $data = $request->validated();

        Schedule::create([
            'course_id'   => $data['course_id'],
            'day_of_week' => $data['day_of_week'],
            'start_time'  => $data['start_time'],
            'end_time'    => $data['end_time'],
            'note'        => $data['note'] ?? null,
        ]);

        return redirect()->route('admin.schedules.index')
            ->with('success', 'Schedule created.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $schedule = Schedule::with('course')->findOrFail($id);

        // ✅ يتوافق مع: resources/js/Pages/admin/Schedules/Show.vue
        return Inertia::render('admin/Schedules/Show', [
            'schedule' => $schedule,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $schedule = Schedule::with('course')->findOrFail($id);
        $courses = Course::orderBy('name')->get(['id', 'name']);
        $days = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];

        // ✅ يتوافق مع: resources/js/Pages/admin/Schedules/Edit.vue
        return Inertia::render('admin/Schedules/Edit', [
            'schedule' => $schedule,
            'courses' => $courses,
            'days' => $days,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateScheduleRequest $request, string $id)
    {
        $schedule = Schedule::findOrFail($id);
        $data = $request->validated();

        $schedule->update([
            'course_id'   => $data['course_id'],
            'day_of_week' => $data['day_of_week'],
            'start_time'  => $data['start_time'],
            'end_time'    => $data['end_time'],
            'note'        => $data['note'] ?? null,
        ]);

        return redirect()->route('admin.schedules.index')
            ->with('success', 'Schedule updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $schedule = Schedule::findOrFail($id);
        $schedule->delete();

        return redirect()->route('admin.schedules.index')
            ->with('success', 'Schedule deleted.');
    }
}
