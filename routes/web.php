<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Student\StudentController;
use App\Http\Controllers\Teacher\TeacherController;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| الصفحة الرئيسية
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin'       => Route::has('login'),
        'canRegister'    => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion'     => PHP_VERSION,
    ]);
});

/*
|--------------------------------------------------------------------------
| مجموعة الإدارة (Admin)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

        // المستخدمون
        Route::get('/users', [AdminController::class, 'usersIndex'])->name('users.index');
        Route::get('/users/create', [AdminController::class, 'createUser'])->name('users.create');
        Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
        Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit');
        Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');

        // الطلاب
        Route::get('/students', [AdminController::class, 'studentsIndex'])->name('students.index');
        Route::get('/students/create', [AdminController::class, 'createStudent'])->name('students.create');
        Route::post('/students', [AdminController::class, 'storeStudent'])->name('students.store');
        Route::get('/students/{student}/edit', [AdminController::class, 'editStudent'])->name('students.edit');
        Route::put('/students/{student}', [AdminController::class, 'updateStudent'])->name('students.update');
        Route::delete('/students/{student}', [AdminController::class, 'destroyStudent'])->name('students.destroy');

        // ترميز الوجوه
        Route::post('/students/encode-faces', [AdminController::class, 'encodeFaces'])->name('students.encode');

        // الدورات
        Route::get('/courses', [AdminController::class, 'coursesIndex'])->name('courses.index');
        Route::get('/courses/create', [AdminController::class, 'coursesCreate'])->name('courses.create');
        Route::post('/courses', [AdminController::class, 'coursesStore'])->name('courses.store');
        Route::get('/courses/{course}/edit', [AdminController::class, 'editCourse'])->name('courses.edit');
        Route::put('/courses/{course}', [AdminController::class, 'updateCourse'])->name('courses.update');
        Route::delete('/courses/{course}', [AdminController::class, 'destroyCourse'])->name('courses.destroy');

        // الحضور
        Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');

        // الجداول
        Route::get('/schedules/export', [ScheduleController::class, 'export'])->name('schedules.export');
        Route::get('/schedules/export-pdf', [ScheduleController::class, 'exportPdf'])->name('schedules.exportPdf');
        Route::resource('schedules', ScheduleController::class);
    });

/*
|--------------------------------------------------------------------------
| مجموعة المعلّم (Teacher)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'role:teacher'])
    ->prefix('teacher')
    ->name('teacher.')
    ->group(function () {

        Route::get('/dashboard', [TeacherController::class, 'dashboard'])->name('dashboard');

        Route::get('/courses/{course}', [TeacherController::class, 'showCourse'])->name('courses.show');

        Route::get('/students/create', [TeacherController::class, 'createStudent'])->name('students.create');
        Route::post('/students', [TeacherController::class, 'storeStudent'])->name('students.store');

        Route::get('/courses/create', [TeacherController::class, 'coursesCreate'])->name('courses.create');
        Route::post('/courses', [TeacherController::class, 'coursesStore'])->name('courses.store');
        Route::post('/courses/{course}/enroll', [TeacherController::class, 'enrollStudent'])->name('courses.enroll');

        // الجداول
        Route::post('/courses/{course}/schedules', [TeacherController::class, 'storeSchedule'])->name('schedules.store');

        // جلسة الحضور
        Route::get('/courses/{course}/attendance', [TeacherController::class, 'startAttendanceSession'])->name('attendance.start');
        Route::post('/attendance/mark', [TeacherController::class, 'markAttendance'])->name('attendance.mark');
        Route::post('/courses/{course}/attendance/end', [TeacherController::class, 'endAttendanceSession'])->name('attendance.end');

        // 🔐 OTP (للمعلم فقط)
        Route::get('/attendance/{course}/otp', [TeacherController::class, 'showOtpVerification'])
            ->name('attendance.otp.show');

        Route::post('/attendance/otp/verify', [TeacherController::class, 'verifyOtpManually'])
            ->name('attendance.otp.verify');
    });

/*
|--------------------------------------------------------------------------
| مجموعة الطالب (Student)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'role:student'])
    ->prefix('student')
    ->name('student.')
    ->group(function () {

        Route::get('/dashboard', [StudentController::class, 'dashboard'])->name('dashboard');
        Route::get('/courses/{course}', [StudentController::class, 'showCourse'])->name('courses.show');

        // ❌ لا يوجد OTP هنا (قرار أمني)
    });

/*
|--------------------------------------------------------------------------
| إعادة التوجيه بعد تسجيل الدخول
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', function () {
    $user = Auth::user();

    if ($user->role === 'admin')   return redirect()->route('admin.dashboard');
    if ($user->role === 'teacher') return redirect()->route('teacher.dashboard');
    if ($user->role === 'student') return redirect()->route('student.dashboard');

    return redirect('/');
})->middleware(['auth', 'verified'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| الملف الشخصي
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
