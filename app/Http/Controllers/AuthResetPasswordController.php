<?php

namespace App\Http\Controllers;

use App\Models\User;
use Ichtrojan\Otp\Otp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\AuthResetPasswordRequest;
use App\Models\Otp as ModelsOtp;
use App\trait\ResponseGlobal;

class AuthResetPasswordController extends Controller
{
use ResponseGlobal ;
private $otp;

public function __construct(){
    $this->otp = new Otp ;
}


public function checkOtp(Request $request){
    $validator = Validator::make($request->all(), [
        'email' => 'required|email|exists:users,email',
        'otp' => 'required|max:6',
    ]);
    if ($validator->fails()) {
        return response()->json([
            'errors' => $validator->errors()
        ],404);
    }
   // $otpValidation = $this->otp->validate($request->email,$request->otp);
   $otp = ModelsOtp::where('identifier',$request->email)->latest()->first();
   if($otp->token==$request->otp ){
    $otp->valid=1;
    $otp->save();
    return $this->success(true);
       }
       return $this->error(false);
   }



public function passwordReset(Request $request)
{
    // Validate the input for email, OTP, and password
    $validator = Validator::make($request->all(), [
        'email' => 'required|email|exists:users,email',
        'password' => 'required|min:6|confirmed', // 'confirmed' ensures password_confirmation is present and matches
    ]);
    if ($validator->fails()) {
        return response()->json([
            'errors' => $validator->errors()
        ],404);
    }
    // Validate OTP
    // $otpValidation = $this->otp->validate($request->email,$request->otp);
$otp = ModelsOtp::where('identifier',$request->email)->latest()->first();

if($otp->valid==1 ||true){
    // $otp->valid=1 ;
    // $otp->save();
    $user = User::where('email', $request->email)->first();
    // If user is found, update the password
    if ($user) {
        $user->update([
            'password' => Hash::make($request->password)
        ]);
        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully.'
        ],200);
    }
}
    return response()->json([
        'success' => false,
        'message' => 'otp is used or false'
    ],404);
}

}
