<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Comment;
use App\Models\Share;
use App\Models\Interaction;
use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function updateUser(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'fullname'  => 'nullable|string|max:255',
            'username'  => 'nullable|string|max:255|unique:users,username,' . $user->id,
            'email'     => 'nullable|email|unique:users,email,' . $user->id,
            'password'  => 'nullable|min:6',
            'image'     => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($request->has('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->update($request->except(['password', 'image']));

        if ($request->hasFile('image')) {
            if ($user->profilePicture) {
                $user->profilePicture->delete();
            }

            $path = $request->file('image')->store('profile_pictures', 'public');

            $asset = Asset::create([
                'user_id'       => $user->id,
                'assetable_id'  => $user->id,
                'assetable_type' => User::class,
                'type'          => 'image',
                'path'          => $path
            ]);
        }

        return response()->json([
            'message' => 'Profile updated successfully',
            'user'    => $user,
            'asset'   => $asset ?? null
        ]);
    }

    public function addComment(Request $request)
    {
        $request->validate([
            'post_id' => 'required|exists:posts,id',
            'content' => 'required|string',
        ]);

        $comment = Comment::create([
            'user_id' => Auth::id(),
            'post_id' => $request->post_id,
            'content' => $request->content,
        ]);

        return response()->json(['message' => 'Comment added', 'comment' => $comment]);
    }

    public function sharePost(Request $request)
    {
        $request->validate([
            'post_id' => 'required|exists:posts,id',
        ]);

        $user = Auth::user();
        $postId = $request->post_id;

        if (Share::where('user_id', $user->id)->where('post_id', $postId)->exists()) {
            return response()->json(['message' => 'You have already shared this post'], 409);
        }

        $share = Share::create([
            'user_id' => $user->id,
            'post_id' => $postId,
        ]);

        return response()->json(['message' => 'Post shared', 'share' => $share]);
    }

    public function savePost(Request $request)
    {
        $request->validate([
            'post_id' => 'required|exists:posts,id',
        ]);

        $user = Auth::user();
        $postId = $request->post_id;

        if (Interaction::where('user_id', $user->id)->where('interactionable_id', $postId)->where('interactionable_type', 'post')->exists()) {
            return response()->json(['message' => 'Post already saved'], 409);
        }

        $bookmark = Interaction::create([
            'user_id' => $user->id,
            'interactionable_id' => $postId,
            'interactionable_type' => 'post',
            'type' => 'bookmark',
        ]);

        return response()->json(['message' => 'Post saved', 'bookmark' => $bookmark]);
    }

    public function UserBookmarks($postId)
    {
        $user = Auth::user();

        $bookmarks = $user->Interaction()->get();

        $bookmarks = $bookmarks->map(fn($like) => $like->$postId);

        return $bookmarks;
    }
}
