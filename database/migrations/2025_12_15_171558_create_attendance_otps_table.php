<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('attendance_otps', function (Blueprint $table) {
            $table->id();

            // الطالب المرتبط بالرمز
            $table->foreignId('student_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // سجل الحضور
            $table->foreignId('attendance_id')
                ->constrained('attendances')
                ->cascadeOnDelete();

            // 🔐 تخزين hash وليس الرمز نفسه
            $table->string('otp_hash');

            // صلاحية الرمز
            $table->timestamp('expires_at');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_otps');
    }
};
