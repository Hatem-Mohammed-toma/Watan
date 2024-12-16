<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\CommentRequest;
use App\Models\Post;
use App\Models\User;
use App\Models\Comment;
use App\trait\ResponseGlobal;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ApiCommentController extends Controller
{
    use ResponseGlobal ;
    public function store(CommentRequest $request, $postId)
    {
        $user = auth('api')->user();

        $post = Post::find($postId);
        if(!$post){
            return $this->error('Operation failed',400,'Post not found');
        }
        // Create and save the comment
        $comment = Comment::create([
            'comment' => $request->comment,
            'user_id' => $user->id,
            'post_id' => $postId,
        ]);
        return $this->success($comment);
    }

    public function pendingComments()
    {
        $comments = Comment::with(['user:id,profile_photo', 'post:id,title'])
                           ->where('status', 'pending')
                           ->get()
                           ->map(function ($comment) {
                               // Only add 'storage/' path if the profile photo is not a complete URL
                               if ($comment->user && $comment->user->profile_photo) {
                                   $comment->user->profile_photo = Str::startsWith($comment->user->profile_photo, 'http')
                                       ? $comment->user->profile_photo
                                       : asset('storage/' . $comment->user->profile_photo);
                               }
                               return $comment;
                           });

                return $this->success($comments);

    }
    
    public function acceptedComments()
    {
        $comments = Comment::with([
            'user:id,name,profile_photo', // Include 'name' and 'profile_photo' from the User model
            'post:id,title' // Include 'title' from the Post model
        ])
        ->where('status', 'accepted')
        ->get()
        ->map(function ($comment) {
            // Transform profile photo path to a full URL
            if ($comment->user && $comment->user->profile_photo) {
                $comment->user->profile_photo = Str::startsWith($comment->user->profile_photo, 'http')
                    ? $comment->user->profile_photo
                    : asset('storage/' . $comment->user->profile_photo);
            }

            return [
                'id' => $comment->id,
                'comment' => $comment->comment,
                'user_id' => $comment->user_id,
                'post_id' => $comment->post_id,
                'status' => $comment->status,
                'user' => [
                    'id' => $comment->user->id,
                    'name' => $comment->user->name,
                    'profile_photo' => $comment->user->profile_photo,
                ],
                'post' => [
                    'id' => $comment->post->id,
                    'title' => $comment->post->title,
                ],
            ];
        });

    return $this->success($comments);

    }
    public function updateStatus(Request $request, $status, $id)
    {

        $comment = Comment::find($id);
        if (!$comment) {
            return $this->error('Operation failed',400,'Comment not found');
        }
        // If status is 'canceled', delete the comment
        if ($status === 'canceled') {
          $data= $comment->delete();
            return $this->success($data);
        }
        // If status is 'accepted', update the comment's status
        $comment->update([
            "status" => $status,
        ]);
        return $this->success($comment);
    }

    public function deleteComment(Request $request, $id)
    {
        $user = auth('api')->user();
        // Find the comment by ID
        $comment = Comment::find($id);
        // Check if the comment exists
        if (!$comment) {
            return $this->error('Operation failed',400,'Comment not found');
        }
        // Check if the comment belongs to the user
        if ($comment->user_id !== $user->id) {
            return $this->error('Operation failed',400,'This user does not own this comment');
        }
        // Delete the comment
       $data= $comment->delete();
       return $this->success($data);
    }

    public function update_user_comment(CommentRequest $request, $commentId)
    {

        $user = auth('api')->user();
        // Find the comment by its ID
        $comment = Comment::find($commentId);
        if (!$comment) {
            return $this->error('Operation failed',400,'Comment not found');
        }
        // Ensure the comment belongs to the user
        if ($comment->user_id !== $user->id) {
            return $this->error('Operation failed',400,'This user does not own this comment');
        }
        // Update the comment
        $comment->update([
            "comment" => $request->comment
        ]);
        return $this->success($comment);

    }

    public function commentsByPostId($postId)
    {
        // Query to get comments with status 'accepted' for a specific post
        $comments = Comment::with('user:id,name,profile_photo') // Include `name` and `profile_photo` from the User model
        ->where('post_id', $postId)
        ->where('status', 'accepted')
        ->get()
        ->map(function ($comment) {
            // Transform profile photo path to a full URL
            if ($comment->user && $comment->user->profile_photo) {
                $comment->user->profile_photo = Str::startsWith($comment->user->profile_photo, 'http')
                    ? $comment->user->profile_photo
                    : asset('storage/' . $comment->user->profile_photo);
            }

            return [
                'id' => $comment->id,
                'comment' => $comment->comment,
                'user_id' => $comment->user_id,
                'post_id' => $comment->post_id,
                'status' => $comment->status,
                'user' => [
                    'id' => $comment->user->id,
                    'name' => $comment->user->name,
                    'profile_photo' => $comment->user->profile_photo,
                ],
            ];
        });

    return $this->success($comments);


    }

}
