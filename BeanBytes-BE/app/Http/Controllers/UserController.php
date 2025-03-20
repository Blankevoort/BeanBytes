<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use App\Models\Asset;
use App\Models\Share;
use App\Models\Comment;
use App\Models\Interaction;
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

    public function addLike(Request $request)
    {
        $user = auth()->user();
        $post = Post::find($request->post_id);

        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        $existingLike = Interaction::where('user_id', $user->id)
            ->where('interactionable_id', $post->id)
            ->where('interactionable_type', Post::class)
            ->where('type', 'like')
            ->first();

        if ($existingLike) {
            $existingLike->delete();
            $message = 'Post Like Removed';
        } else {
            Interaction::create([
                'user_id' => $user->id,
                'interactionable_id' => $post->id,
                'interactionable_type' => Post::class,
                'type' => 'like',
            ]);
            $message = 'Post liked';
        }

        $likesCount = Interaction::where('interactionable_id', $post->id)
            ->where('interactionable_type', Post::class)
            ->where('type', 'like')
            ->count();

        return response()->json([
            'message' => $message,
            'likes_count' => $likesCount,
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

        if (
            Interaction::where('user_id', $user->id)
            ->where('interactionable_id', $postId)
            ->where('interactionable_type', Post::class)
            ->where('type', 'share')
            ->exists()
        ) {
            return response()->json(['message' => 'You have already shared this post'], 409);
        }

        $share = Interaction::create([
            'user_id' => $user->id,
            'interactionable_id' => $postId,
            'interactionable_type' => Post::class,
            'type' => 'share',
        ]);

        return response()->json([
            'message' => 'Post shared',
            'share' => $share,
        ]);
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

        $bookmarkedPosts = Post::whereHas('interactions', function ($query) use ($user) {
            $query->where('user_id', $user->id)
                ->where('interactionable_type', 'post')
                ->where('type', 'bookmark');
        })
            ->with([
                'user:id,username',
                'tags:id,name',
                'assets'
            ])
            ->withCount('comments')
            ->get()
            ->map(function ($post) use ($user) {
                return [
                    'id' => $post->id,
                    'content' => $post->content,
                    'fullCode' => $post->fullCode,
                    'visibility' => $post->visibility,
                    'created_at' => $post->created_at,
                    'user' => [
                        'id' => $post->user->id,
                        'username' => $post->user->username,
                    ],
                    'tags' => $post->tags->pluck('name'),
                    'likes_count' => Interaction::where('interactionable_id', $post->id)
                        ->where('interactionable_type', Post::class)
                        ->where('type', 'like')
                        ->count(),
                    'comments_count' => $post->comments_count,
                    'shares_count' => Interaction::where('interactionable_id', $post->id)
                        ->where('interactionable_type', Post::class)
                        ->where('type', 'share')
                        ->count(),
                    'isLiked' => Interaction::where('user_id', $user->id)
                        ->where('interactionable_id', $post->id)
                        ->where('interactionable_type', Post::class)
                        ->where('type', 'like')
                        ->exists(),
                    'isShared' => Interaction::where('user_id', $user->id)
                        ->where('interactionable_id', $post->id)
                        ->where('interactionable_type', Post::class)
                        ->where('type', 'share')
                        ->exists(),
                    'isBookmarked' => true, // Since we're only fetching bookmarked posts
                    'assets' => $post->assets->map(function ($asset) {
                        return [
                            'id' => $asset->id,
                            'type' => $asset->type,
                            'url' => $asset->getRawOriginal('path'),
                        ];
                    })->values(),
                ];
            });

        return response()->json($bookmarkedPosts);
    }
}
