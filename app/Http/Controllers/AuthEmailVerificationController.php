<?php

namespace App\Http\Controllers;

use App\Models\User;
use Ichtrojan\Otp\Otp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Notifications\EmailVerificationNotification;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Models\Otp as ModelsOtp;


class AuthEmailVerificationController extends Controller
{

private $otp ;
public function __construct(){
    $this->otp = new Otp();
}

public function sendEmailVerification(Request $request)
{
    $validator = Validator::make($request->all(), [
        'email' => 'required|email|exists:users,email',
    ]);

    // If validation fails, return a JSON response with the errors
    if ($validator->fails()) {
        return response()->json([
            'errors' => $validator->errors()
        ],404);
    }
    // Retrieve the user by email
    $user = User::where('email', $request->email)->first();
    // Check if the user exists
    if ($user) {
        // Send email verification notification
        $user->notify(new EmailVerificationNotification());
        // Return success response
        $success['success'] = "code is send";
        return response()->json($success, 200);
    } else {
        // If the user doesn't exist, return an error response
        return response()->json([
            'success' => false,
            'message' => 'User not found.'
        ],404);
    }
}

public function email_verification(Request $request)
{
    // Validate email and OTP
    $validator = Validator::make($request->all(), [
        'email' => 'required|email|exists:users,email',
        'otp' => 'required|max:6'
    ]);

    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()], 400);
    }
    // Validate OTP
    $user = User::where('email', $request->email)->first();
    if ($user->email_verified_at) {
        return response()->json([
            'success' => true,
            'message' => 'Email is already verified.'
        ],200);
    }

    // $otpValidation = $this->otp->validate($request->email, $request->otp);
    $otp = ModelsOtp::where('identifier',$request->email)->latest()->first();
    if($otp->token==$request->otp){
        // $otp->valid=1 ;
        // $otp->save();
        $user = User::where('email', $request->email)->first();
        // If user is found, update the password
        if ($user) {
            $user->update(['email_verified_at' => now()]);
            return response()->json([
                'success' => true,
                'message' => 'Email verified successfully.'
            ], 200);
        }
    }
       return response()->json([
            'success' => false,
            'message' => 'otp is used or false'
        ], 404);

   
}

}
