<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    public function notice(){
        return response()->json(['message' => 'You should verify your email']);
    }

    public function verify(EmailVerificationRequest $request){
        $request->fulfill();

        return response()->json(['message' => 'Email verified successfully!']);
    }

    public function send(Request $request) {
        $request->user()->sendEmailVerificationNotification();

        return response()->json(['message' => 'Verification link sent!']);
    }

}
