<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\trait\ResponseGlobal;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Auth\RegisterRequest;
use App\Notifications\EmailVerificationNotification;

class AuthRegisterController extends Controller
{
use  ResponseGlobal;
    public function register(RegisterRequest $request)
    {


       $password = Hash::make($request->password);
       $user = User::create([
        "name" => $request->name,
        "email" => $request->email,
        "password" => $password
    ]);

    $token = JWTAuth::fromUser($user);
    $user->update(['access_token' => $token]);

    //    $user->notify(new EmailVerificationNotification());

        return $this->success($user);
    }

    // public function name(){
    //     return "mustafa" ;
    // }
}
