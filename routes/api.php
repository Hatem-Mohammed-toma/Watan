<?php

use App\Http\Controllers\ApiCategoryController;
use App\Http\Controllers\ApiCommentController;
use App\Http\Controllers\ApiCountryController;
use App\Http\Controllers\ApiPostController;
use App\Http\Controllers\ApiProductController;
use App\Http\Controllers\AuthEmailVerificationController;
use App\Http\Controllers\AuthForgetPasswordController;
use App\Http\Controllers\AuthloginController;
use App\Http\Controllers\AuthLogoutController;
use App\Http\Controllers\AuthRegisterController;
use App\Http\Controllers\AuthResetPasswordController;
use App\Http\Controllers\EditProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['valid_auth'])->group(function () {
    Route::get('/profile', function (Request $request) {
        $user = auth('api')->user();
        return response()->json([
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'photo' => $user->profile_photo ? asset('storage/' . $user->profile_photo) : null, // Full photo URL or null
            ],
        ]);
    });
});



Route::post('/register', [AuthRegisterController::class, 'register']);

Route::post('/name', [AuthRegisterController::class, 'name']); // test

Route::post('/login', [AuthloginController::class, 'login']);
Route::post('/logout', [AuthLogoutController::class, 'logout'])->middleware('valid_auth');

Route::post('/editprofile', [EditProfileController::class, 'updateProfile'])->middleware('valid_auth');


Route::post('/email-verification', [AuthEmailVerificationController::class, 'email_verification']); // code from register
Route::post('/send-email-verification', [AuthEmailVerificationController::class, 'sendEmailVerification']);// with email give code

Route::post('password/forgot-password',[AuthForgetPasswordController::class ,'forgotpassword']);
Route::post('password/reset',[AuthResetPasswordController::class, 'passwordReset']);

Route::post('password/checkOtp',[AuthResetPasswordController::class, 'checkOtp']);


////////////////////////////////////////////////////////////////////////

Route::controller(ApiPostController::class)->group(function(){

    Route::post('posts','store')->middleware('valid_auth'); // add post with acces_token ;
    Route::get('posts/users','postuser'); // all user    status accepted
    Route::get('posts/admins', 'allposts')->middleware('api_auth'); // admin status pending
    // الفانكشن ثابته بس انا بغير في ال حاله
    //  acceptهي هتمشي على كل البوستات و هتساعد الادمن انو يقدر يلغي
     Route::put('posts/{status}/{id}','update')->middleware('api_auth'); // midlleware api_auth // دي من غير canceled or accepted
     Route::delete('posts/{id}','delete_user')->middleware('valid_auth');//->middleware('auth:sanctum');     // بياخد ال access عشان ياكد ان البوست ده بتاعو وميمسحش حاجه هو مش رفعها
     Route::put('posts/update/users/{id}','update_user_post')->middleware('valid_auth');//->middleware('auth:sanctum');  // acess

     Route::get('posts/latestnews','LatestNews');//->middleware('api_auth');
});

Route::controller(ApiCategoryController::class)->group(function(){
    Route::post('category/store','store');
    Route::post('check-code_name', 'checkCategoryByCodeAndalternatives');// دي اللي هنشتغل بيها
    Route::post('check-by-code-name','chekCode');
    Route::post('get-alternatives-code-name','getAlternatives');
    Route::delete('categories/{id}','deleteCategory');

    Route::get('category/all','getAllCategories');
    Route::get('category/{name}','getCategoryName');
    Route::get('/categories/type/{categoryType}','getCategoryByType');

});

Route::controller(ApiProductController::class)->group(function(){
    Route::post("create_product","store");
    Route::put("product/{id}","update");

    Route::delete("products/{id}","deleteProduct");

    Route::get("products/all","getAllProducts");
    Route::get("products/{product_type}","getProductsByType");



});

Route::controller(ApiCountryController::class)->group(function () {
    Route::post('create_country','store'); // Store a new country/event
     Route::get('countries/page1','index1');
     Route::get('countries/page2/{country_name}','index2'); // List all countries/events web
     Route::get('countries/page4/{country_name}','indexsabry'); // List all countries/eventsvsabry
     Route::get('countries/page3/{city_name}','index3'); // List all countries/events

     Route::put('/events/{id}','update');   // Update event with old photo deletion
     Route::delete('/country/{id}','destroy'); // Delete event with photo removal

     Route::post('/link-ai','linkAi');

});

Route::controller(ApiCommentController::class)->group(function(){
    Route::post('comments/{postId}','store')->middleware('valid_auth'); //->middleware('auth:sanctum');
    Route::get('/comments/pending', 'pendingComments')->middleware('api_auth'); //admin
    Route::get('/comments/accepted','acceptedComments');
    Route::put('/comments/{status}/{id}','updateStatus')->middleware('api_auth'); //admin
    Route::delete('/comments/{id}','deleteComment')->middleware('valid_auth');

    Route::get('comments_posts/{postId}','commentsByPostId');
    Route::put('/comments/{commentId}','update_user_comment')->middleware('valid_auth');

    Route::get('test','test');
});



