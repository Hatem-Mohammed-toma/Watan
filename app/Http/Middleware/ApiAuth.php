<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth('api')->user();
             if($user !== null){
                 if($user->user_type == 'admin'){
                     return $next($request);
                 }else{
                     return response()->json([
                         "msg"=>" user type not correct",
                       ],300);
                 }
            }else{
             return response()->json([
                "msg" => "Unauthorized. Please login with a valid token.",
               ],401);
            }

    }
}
