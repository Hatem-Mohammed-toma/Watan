<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\EditProfileRequest;
use App\Models\User;
use App\trait\ResponseGlobal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class EditProfileController extends Controller
{
    use ResponseGlobal ;
    public function updateProfile(EditProfileRequest $request)
{
    $user = auth('api')->user();

    // Update profile photo if provided
    if ($request->hasFile('profile_photo')) {
        if ($user->profile_photo){
            Storage::delete($user->profile_photo); // Delete old photo if exists
        }
        $photoPath = Storage::putFile('profile_photos', $request->file('profile_photo'));
        $user->profile_photo = $photoPath;
    }

    // Update name if provided
    if ($request->filled('name')) {
        $user->name = $request->input('name');
    }

    $user->save();

    return $this->success([
        'name' => $user->name,
        'profile_photo' => $user->profile_photo ? url('storage/' . $user->profile_photo) : null,
    ]);

}
}
