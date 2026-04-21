<?php


namespace App\Http\Controllers;


use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

class EmailController {
    public function verifyEmail(EmailVerificationRequest $request) {
        $request->fulfill();

        return redirect('/dashboard/home');
    }
    public function sendEmailVerification(Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('status', 'verification-link-sent');
    }
}
