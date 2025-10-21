<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\AbsentStudentVerificationMail;
use App\Models\User;

class SendAbsentVerificationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $student;
    protected $verificationCode;

    /**
     * Create a new job instance.
     */
    public function __construct(User $student, string $verificationCode)
    {
        $this->student = $student;
        $this->verificationCode = $verificationCode;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // هذا هو الكود المسؤول عن إرسال الإيميل
        Mail::to($this->student->email)->send(
            new AbsentStudentVerificationMail($this->student->name, $this->verificationCode)
        );
    }
}