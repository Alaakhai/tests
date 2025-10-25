
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
        Schema::create('course_student', function (Blueprint $table) {
            $table->id();

            // العلاقة مع المادة
            $table->foreignId('course_id')->constrained()->onDelete('cascade');

            // العلاقة مع الطالب
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');

            // ✅ إضافة العلاقة مع الفصل الدراسي
            $table->foreignId('semester_id')->nullable()->constrained('semesters')->onDelete('cascade');

            $table->timestamps();

            // ✅ منع تسجيل الطالب نفس المادة في نفس الفصل مرتين
            $table->unique(['student_id', 'course_id', 'semester_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_student');
    }
};
