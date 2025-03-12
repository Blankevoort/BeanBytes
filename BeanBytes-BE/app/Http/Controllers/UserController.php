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

        $existingBookmark = Interaction::where('user_id', $user->id)
            ->where('interactionable_id', $postId)
            ->where('interactionable_type', 'post')
            ->where('type', 'bookmark')
            ->first();

        if ($existingBookmark) {
            $existingBookmark->delete();
            return response()->json(['message' => 'Post unsaved']);
        }

        $bookmark = Interaction::create([
            'user_id' => $user->id,
            'interactionable_id' => $postId,
            'interactionable_type' => 'post',
            'type' => 'bookmark',
        ]);

        return response()->json(['message' => 'Post saved', 'bookmark' => $bookmark]);
    }

    public function UserBookmarks()
    {
        $user = Auth::user();

        $bookmarkedPosts = Interaction::where('user_id', $user->id)
            ->where('interactionable_type', 'post')
            ->where('type', 'bookmark')
            ->with(['interactionable.user:id,username', 'interactionable.comments', 'interactionable.shares', 'interactionable.interactions'])
            ->get()
            ->map(fn($interaction) => [
                'id' => $interaction->interactionable->id,
                'content' => $interaction->interactionable->content,
                'visibility' => $interaction->interactionable->visibility,
                'created_at' => $interaction->interactionable->created_at,
                'user' => [
                    'id' => $interaction->interactionable->user->id,
                    'username' => $interaction->interactionable->user->username,
                ],
                'comments_count' => $interaction->interactionable->comments->count(),
                'shares_count' => $interaction->interactionable->shares->count(),
                'likes_count' => $interaction->interactionable->interactions->where('type', 'like')->count(),
                'isBookmarked' => true,
            ]);

        return response()->json($bookmarkedPosts);
    }
}
