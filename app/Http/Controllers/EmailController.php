<?php


namespace App\Http\Controllers;


use Illuminate\Foundation\Auth\EmailVerificationRequest;

class EmailController extends Controller {
    public function verifyEmail(EmailVerificationRequest $request) {
        $request->fulfill();

        return redirect('/dashboard/home');
    }

    public function sendEmailVerification(EmailVerificationRequest $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('message', 'Verification link sent!');
    }
}
