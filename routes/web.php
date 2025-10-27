<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Student\StudentController;
use App\Http\Controllers\Teacher\TeacherController;
use Illuminate\Support\Facades\Auth; // 👈 🔑 استدعاء أساسي لمنطق التوجيه الجديد

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [AdminController::class, 'usersIndex'])->name('users.index');

    // *** إضافة راوتات إدارة المستخدمين ***
    Route::get('/users/create', [AdminController::class, 'createUser'])->name('users.create');
    Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
    Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit');
    Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
    // *** نهاية الإضافة ***

    Route::post('/students/encode-faces', [AdminController::class, 'encodeFaces'])->name('students.encode');

    // --- إدارة الدورات ---
    Route::get('/courses', [AdminController::class, 'coursesIndex'])->name('courses.index');
    Route::get('/courses/create', [AdminController::class, 'coursesCreate'])->name('courses.create');
    Route::post('/courses', [AdminController::class, 'coursesStore'])->name('courses.store');

    // ✅ ✅ ✅ الإضافات المطلوبة (دون تغيير محتوى الباقي):
    Route::get('/courses/{course}/edit', [AdminController::class, 'editCourse'])->name('courses.edit');
    Route::put('/courses/{course}', [AdminController::class, 'updateCourse'])->name('courses.update');

    // --- إدارة الطلاب ---
    Route::get('/students', [AdminController::class, 'studentsIndex'])->name('students.index');
    Route::get('/students/create', [AdminController::class, 'createStudent'])->name('students.create');
    Route::post('/students', [AdminController::class, 'storeStudent'])->name('students.store');
});

Route::middleware(['auth', 'verified', 'role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/dashboard', [TeacherController::class, 'dashboard'])->name('dashboard');
    Route::get('/courses/{course}', [TeacherController::class, 'showCourse'])->name('courses.show');
    Route::get('/students/create', [TeacherController::class, 'createStudent'])->name('students.create');
    Route::post('/students', [TeacherController::class, 'storeStudent'])->name('students.store');
    // In routes/web.php Teacher's Group
    Route::get('teacher/courses/create', [TeacherController::class, 'coursesCreate'])->name('courses.create');
    Route::post('/courses', [TeacherController::class, 'coursesStore'])->name('courses.store');
    Route::post('/courses/{course}/enroll', [TeacherController::class, 'enrollStudent'])->name('courses.enroll');

    // --- Add this new route for storing a schedule ---
    Route::post('/courses/{course}/schedules', [TeacherController::class, 'storeSchedule'])->name('schedules.store');
    // --- Add these two new routes for the attendance session ---
    Route::get('/courses/{course}/attendance', [TeacherController::class, 'startAttendanceSession'])->name('attendance.start');
    Route::post('/attendance/mark', [TeacherController::class, 'markAttendance'])->name('attendance.mark');
});

// You can also create a dedicated student group if you plan to add more routes
Route::middleware(['auth', 'verified', 'role:student'])->prefix('student')->name('student.')->group(function () {
    // Example for future routes: Route::get('/my-attendance', ...);
    Route::get('/dashboard', [StudentController::class, 'dashboard'])->name('dashboard');
});

// Student specific routes
Route::middleware(['auth', 'verified', 'role:student'])->prefix('student')->name('student.')->group(function () {
    Route::get('/courses/{course}', [StudentController::class, 'showCourse'])->name('courses.show');
});

// 🔑🔑 الكتلة المضافة: توجيه المستخدمين إلى لوحة القيادة الصحيحة بعد تسجيل الدخول 🔑🔑
Route::get('/dashboard', function () {
    $user = Auth::user();

    if ($user->role === 'admin') {
        return redirect()->route('admin.dashboard');
    } elseif ($user->role === 'teacher') {
        return redirect()->route('teacher.dashboard');
    } elseif ($user->role === 'student') {
        return redirect()->route('student.dashboard');
    }

    // توجيه احتياطي في حال وجود مشكلة
    return redirect('/'); 
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';