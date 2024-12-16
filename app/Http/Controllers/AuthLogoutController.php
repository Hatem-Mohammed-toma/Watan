<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthLogoutController extends Controller
{
    public function logout(Request $request)
    {
        $user = auth('api')->user();

          if($user !==null){
              $user->update([
               "access_token"=>null
              ]);
              $data=[
               "msg"=>"you logged out successfuly",
               "status" => 200,
               "access_token"=> null ,
             ];
             return response()->json($data,200);
           }else{
           $data=[
               "msg"=>"access token not correct",
               "status" => 404,
               "access_token"=>null
             ];
             return response()->json($data,404);
          }
       }

     }

