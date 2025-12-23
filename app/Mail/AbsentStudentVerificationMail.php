<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AbsentStudentVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $otp;
    public string $courseName;

    public function __construct(string $otp, string $courseName)
    {
        $this->otp = $otp;
        $this->courseName = $courseName;
    }

    public function build()
    {
        return $this
            ->subject('رمز التحقق من الحضور')
            ->view('emails.attendance-otp');
    }
}
