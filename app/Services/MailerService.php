<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;

class MailerService
{
    public function sendEmail($to, $subject, $body)
    {
        Mail::raw($body, function ($message) use ($to, $subject) {
            $message->to($to)
                ->subject($subject);
        });

        return true;
    }
}
