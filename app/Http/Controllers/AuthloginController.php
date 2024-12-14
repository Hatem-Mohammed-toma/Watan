<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;
use App\Notifications\LoginNotification;
use App\trait\ResponseGlobal;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;


class AuthloginController extends Controller
{
    use ResponseGlobal ;
    public function login(Request $request)
{

    $credentials = $request->only('email', 'password');

    try {
        if (! $token = JWTAuth::attempt($credentials)) {
            return $this->error('Invalid credentials',401,'email or password is wrong');
        }

        // Get the authenticated user.
        $user = auth()->user();
        $user->access_token=$token;
        $user->save();

        // (optional) Attach the role to the token.
        //  $user->notify(new LoginNotification());
return $this->success($user) ;
     //   return response()->json(compact('token'));
    } catch (JWTException $e) {
        return response()->json(['error' => 'Could not create token'], 500);
    }
}


//         // // Optional: Send a login notification
//         // $user->notify(new LoginNotification());

}
