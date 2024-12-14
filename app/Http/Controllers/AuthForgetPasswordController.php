<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\AuthForgetPasswordRequest;
use App\Notifications\ResetPasswordVerificationNotification;

class AuthForgetPasswordController extends Controller
{

public function forgotpassword(Request $request){

    $validator = Validator::make($request->all(), [
        'email' => 'required|email|exists:users,email'
    ]);

    // If validation fails, return errors
    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors' => $validator->errors()
        ], 422); // 422 Unprocessable Entity for validation errors
    }

    $email = $request->email;
    // Find the user by email
    $user = User::where('email', $email)->first();

    // Check if the user exists
    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'User not found.'
        ], 404);
    }
    // Send the reset password notification
    $user->notify(new ResetPasswordVerificationNotification());

    // Return success response
    $success['success'] = true;
    return response()->json($success, 200);
}

}
