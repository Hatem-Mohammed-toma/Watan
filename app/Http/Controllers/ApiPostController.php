<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\PostRequest;
use App\Models\Post;
use App\Models\User;
use App\trait\ResponseGlobal;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ApiPostController extends Controller
{
use ResponseGlobal ;
    public function store(PostRequest $request)
{
    $user = auth('api')->user();
    // Handle file upload if media is provided
    $mediaPaths = [];
    // Check if multiple files are uploaded
    if ($request->hasFile('media')) {
        foreach ($request->file('media') as $file) {
            // Store each file and push its path to the array
            $mediaPaths[] = Storage::putFile("posts", $file);
        }
    }
    $cleanContent = trim(preg_replace('/\s*\n\s*/', ' ', $request->content)); // Replace all \n with a single space
    $cleanContent = htmlspecialchars($cleanContent, ENT_QUOTES, 'UTF-8'); // Sanitize for HTML
    // Create the post
    $post = Post::create([
        "title" => $request->title,
        "content" =>  $cleanContent,
        "user_id" => $user->id, // Use the authenticated user's ID
        "media" =>json_encode($mediaPaths),  // Save the file path, or null if no file uploaded
        "status" => $user->user_type === 'admin' ? 'accepted' : 'pending', // Set status based on user type
    ]);
    return $this->success($post);
}
// all posts pending for admin
    public function allposts()
    {
        $posts = Post::select('id', 'title', 'content', 'status', 'media', 'user_id')
        ->where('status', 'pending')
        ->with(['user:id,profile_photo']) // Load only profile photo from the User model
        ->get()
        ->map(function ($post) {
            if ($post->media) {
                $mediaPaths = json_decode($post->media, true);
                if (is_array($mediaPaths)) {
                    $post->media = array_map(function ($path) {
                        return asset('storage/' . $path);
                    }, $mediaPaths);
                } else {
                    $post->media = [asset('storage/' . $post->media)];
                }
            }
            if ($post->user && $post->user->profile_photo) {
                $post->user->profile_photo = Str::startsWith($post->user->profile_photo, 'http')
                    ? $post->user->profile_photo
                    : asset('storage/' . $post->user->profile_photo);
            }
            return $post;
        });

        return $this->success($posts);

    }
    // show all posts accepted
    public function postuser()
    {
        // Query to get posts with status 'accepted'
        $posts = Post::select('id', 'title', 'content', 'status', 'media', 'user_id')
            ->where('status', 'accepted')
            ->with(['user:id,profile_photo']) // Load only profile photo from the User model
            ->get()
            ->map(function ($post) {
                // Transform media path to a full URL
                if ($post->media) {
                    $mediaPaths = json_decode($post->media, true);
                    if (is_array($mediaPaths)) {
                        $post->media = array_map(function ($path) {
                            return asset('storage/' . $path);
                        }, $mediaPaths);
                    } else {
                        $post->media = [asset('storage/' . $post->media)];
                    }
                }
                // Transform profile photo path to a full URL if it's not already a complete URL
                if ($post->user && $post->user->profile_photo) {
                     $post->user->profile_photo = Str::startsWith($post->user->profile_photo, 'http')
                        ? $post->user->profile_photo
                        :asset('storage/' . $post->user->profile_photo);
                }
                return $post;
            });

            return $this->success($posts);
    }
///   canceld and accepted by admin //
public function update(Request $request,$status, $id)
{
    $post = Post::find($id);
    if (!$post ){
        return $this->error('Operation failed', 400,'Post_id not found');
    }
    // Check if the status is 'canceled' and delete the post if it is
    if ($status === 'canceled') {
        if ($post->media) {
            // Decode media if stored as JSON
            $mediaPaths = json_decode($post->media, true);
            if (is_array($mediaPaths)) {
                // Delete each file in the array
                foreach ($mediaPaths as $mediaPath) {
                    Storage::delete($mediaPath);
                }
            } else {
                // Handle single media path (fallback)
                Storage::delete($post->media);
            }
        }
        $data = $post->delete();
        return $this->success($data);
    }
}
//// delete_post_user
public function delete_user(Request $request, $id)
{
    $user = auth('api')->user();
    $post = Post::find($id);
    if (!$post ){
        return $this->error('Operation failed', 400,'Post not found');
    }
    if ($post->user_id !== $user->id) {
        return $this->error('Operation failed', 400,'This user does not own this post');
    }
    if ($post->media) {
        // Decode media if stored as JSON
        $mediaPaths = json_decode($post->media, true);

        if (is_array($mediaPaths)) {
            // Delete each file in the array
            foreach ($mediaPaths as $mediaPath) {
                Storage::delete($mediaPath);
            }
        } else {
            // Handle single media path (fallback)
            Storage::delete($post->media);
        }
    }
    $data = $post->delete();
    return $this->success($data);
}
public function update_user_post(PostRequest $request, $id)
{
    $user = auth('api')->user();
    // Find the post by ID
    $post = Post::find($id);
    if (!$post) {
        return $this->error('Operation failed', 400,'Post not found');
    }
    // Ensure the post belongs to the user
    if ($post->user_id !== $user->id) {
        return $this->error('Operation failed', 400,'This user does not own this post');
    }
    // Handle media file upload if provided, delete old file if new one is uploaded
    if ($request->hasFile('media')) {
        // Delete the old media file if it exists
        if ($post->media) {
            Storage::delete($post->media);
        }
        // Store the new media file
        $mediaPath = Storage::putFile("posts", $request->media);
        $post->media = $mediaPath;
    }
    // Update the post
    $post->update([
        "title" => $request->title,
        "content" => $request->content,
        "media" => $post->media, // Update the media path if changed
    ]);
    return $this->success($post);
}

public function LatestNews()
{
    // Query posts where the user is an admin and the status is 'accepted'
    $posts = Post::select('id', 'title', 'content', 'status', 'media', 'user_id')
        ->whereHas('user', function ($query) {
            $query->where('user_type', 'admin'); // Filter posts created by admins
        })
        ->where('status', 'accepted') // Only include posts with 'accepted' status
        ->with(['user:id,profile_photo']) // Load user's profile photo
        ->get()
        ->map(function ($post) {
            // Transform media path to a full URL
            if ($post->media) {
                $mediaPaths = json_decode($post->media, true);
                if (is_array($mediaPaths)) {
                    $post->media = array_map(function ($path) {
                        return asset('storage/' . $path);
                    }, $mediaPaths);
                } else {
                    $post->media = [asset('storage/' . $post->media)];
                }
            }
            // Transform profile photo path to a full URL if necessary
            if ($post->user && $post->user->profile_photo) {
                $post->user->profile_photo = Str::startsWith($post->user->profile_photo, 'http')
                    ? $post->user->profile_photo
                    : asset('storage/' . $post->user->profile_photo);
            }
            return $post;
        });
        return $this->success($posts);

}
}
