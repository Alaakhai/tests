<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table) {

            if (!Schema::hasColumn('schedules', 'teacher_id')) {
                $table->foreignId('teacher_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete()
                    ->after('course_id');
            }

            if (!Schema::hasColumn('schedules', 'classroom_id')) {
                $table->foreignId('classroom_id')
                    ->nullable()
                    ->constrained('classrooms')
                    ->nullOnDelete()
                    ->after('teacher_id');
            }

            if (!Schema::hasColumn('schedules', 'is_active')) {
                $table->boolean('is_active')
                    ->default(true)
                    ->after('end_time');
            }
        });
    }

    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {

            if (Schema::hasColumn('schedules', 'classroom_id')) {
                $table->dropConstrainedForeignId('classroom_id');
            }

            if (Schema::hasColumn('schedules', 'teacher_id')) {
                $table->dropConstrainedForeignId('teacher_id');
            }

            if (Schema::hasColumn('schedules', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }
};
