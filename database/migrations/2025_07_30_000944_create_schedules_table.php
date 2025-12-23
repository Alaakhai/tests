<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();

            // العلاقة مع المادة
            $table->foreignId('course_id')->constrained()->onDelete('cascade');

            // علاقة بالمدرس (teacher)
            $table->foreignId('teacher_id')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');

            // 🔧 إصلاح الخطأ فقط: إزالة الربط بجدول غير موجود
            $table->unsignedBigInteger('semester_id')->nullable();

            // علاقة بالقاعة الدراسية
            $table->foreignId('classroom_id')
                  ->nullable()
                  ->constrained('classrooms')
                  ->onDelete('set null');

            // اليوم والوقت
           $table->string('day_of_week');


            $table->time('start_time');
            $table->time('end_time');

            // حالة تفعيل الحصة
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
