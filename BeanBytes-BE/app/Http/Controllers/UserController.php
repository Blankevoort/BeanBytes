<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use App\Models\Asset;
use App\Models\Comment;
use App\Models\Interaction;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function updateUser(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name'      => 'nullable|string|max:255|unique:users,name,' . $user->id,
            'username'  => 'nullable|string|max:255',
            'email'     => 'nullable|email|max:255|unique:users,email,' . $user->id,
            'phone'     => 'nullable|string|max:20|unique:users,phone,' . $user->id,
            'job_title' => 'nullable|string|max:255',
            'bio'       => 'nullable|string',
            'location'  => 'nullable|string|max:255',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $user->update($request->except(['job_title', 'bio', 'location', 'profile_image']));

        if (!$user->profile) {
            $profile = $user->profile()->create([
                'job_title' => $request->job_title,
                'bio'       => $request->bio,
                'location'  => $request->location,
            ]);
        } else {
            $profile = $user->profile;
            $profile->update([
                'job_title' => $request->job_title,
                'bio'       => $request->bio,
                'location'  => $request->location,
            ]);
        }

        $profileImagePath = null;

        if ($request->hasFile('profile_image')) {
            $image = $request->file('profile_image');
            $imagePath = $image->store('profile_pictures', 'public');

            if ($profile->profileImage) {
                Storage::disk('public')->delete($profile->profileImage->path);
                $profile->profileImage()->delete();
            }

            $profileImage = $profile->profileImage()->create([
                'path' => $imagePath,
                'type' => 'image',
            ]);

            $profileImagePath = asset("storage/{$profileImage->path}");
        } elseif ($profile->profileImage) {
            $profileImagePath = asset("storage/{$profile->profileImage->path}");
        }

        return response()->json([
            'message'       => 'Profile updated successfully',
            'user'          => $user->load('profile'),
            'profile_image' => $profileImagePath
        ]);
    }

    public function addLike(Request $request)
    {
        $user = auth()->user();
        $post = Post::find($request->post_id);

        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        $existingLike = Interaction::where([
            'user_id' => $user->id,
            'interactionable_id' => $post->id,
            'interactionable_type' => Post::class,
            'type' => 'like',
        ])->first();

        if ($existingLike) {
            $existingLike->delete();

            Notification::where([
                'user_id' => $post->user_id,
                'from_user_id' => $user->id,
                'type' => 'like',
                'notifiable_type' => Post::class,
                'notifiable_id' => $post->id,
            ])->delete();

            $message = 'Post Like Removed';
        } else {
            Interaction::create([
                'user_id' => $user->id,
                'interactionable_id' => $post->id,
                'interactionable_type' => Post::class,
                'type' => 'like',
            ]);

            $existingNotification = Notification::where([
                'user_id' => $post->user_id,
                'from_user_id' => $user->id,
                'type' => 'like',
                'notifiable_type' => Post::class,
                'notifiable_id' => $post->id,
            ])->first();

            if (!$existingNotification) {
                Notification::create([
                    'user_id' => $post->user_id,
                    'from_user_id' => $user->id,
                    'type' => 'like',
                    'notifiable_type' => Post::class,
                    'notifiable_id' => $post->id,
                ]);
            }

            $message = 'Post liked';
        }

        $likesCount = Interaction::where([
            'interactionable_id' => $post->id,
            'interactionable_type' => Post::class,
            'type' => 'like',
        ])->count();

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

        $user = Auth::user();
        $post = Post::find($request->post_id);

        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        $comment = Comment::create([
            'user_id' => $user->id,
            'post_id' => $post->id,
            'content' => $request->content,
        ]);

        $existingNotification = Notification::where([
            'user_id' => $post->user_id,
            'from_user_id' => $user->id,
            'type' => 'comment',
            'notifiable_type' => Post::class,
            'notifiable_id' => $post->id,
        ])->first();

        if (!$existingNotification) {
            Notification::create([
                'user_id' => $post->user_id,
                'from_user_id' => $user->id,
                'type' => 'comment',
                'notifiable_type' => Post::class,
                'notifiable_id' => $post->id,
            ]);
        }

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
            return response()->json([
                'message' => 'Post unsaved',
                'post_id' => $postId,
                'is_bookmarked' => false
            ]);
        }

        Interaction::create([
            'user_id' => $user->id,
            'interactionable_id' => $postId,
            'interactionable_type' => 'post',
            'type' => 'bookmark',
        ]);

        return response()->json([
            'message' => 'Post saved',
            'post_id' => $postId,
            'is_bookmarked' => true
        ]);
    }

    public function UserBookmarks()
    {
        $user = Auth::guard('sanctum')->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $bookmarkedPosts = Post::whereHas('interactions', function ($query) use ($user) {
            $query->where('user_id', $user->id)
                ->where('interactionable_type', 'post')
                ->where('type', 'bookmark');
        })
            ->with([
                'user:id,username,name',
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
                        'name' => $post->user->name,
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
                    'isBookmarked' => true,
                    'isFollowed' => $user ? Interaction::where('user_id', $user->id)
                        ->where('interactionable_id', $post->user->id)
                        ->where('interactionable_type', User::class)
                        ->where('type', 'follow')
                        ->exists() : false,
                    'assets' => Asset::where('assetable_id', $post->id)
                        ->where('assetable_type', 'App\Models\Post')
                        ->get()
                        ->map(function ($asset) {
                            return [
                                'id' => $asset->id,
                                'type' => $asset->type,
                                'url' => $asset->getRawOriginal('path'),
                            ];
                        }),
                ];
            });

        return response()->json($bookmarkedPosts);
    }

    public function getNotifications()
    {
        $user = auth()->user();
        $notifications = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($notification) {
                $fromUser = User::find($notification->from_user_id);

                return [
                    'id' => $notification->id,
                    'user_id' => $notification->user_id,
                    'from_user_id' => $notification->from_user_id,
                    'from_user_name' => $fromUser ? $fromUser->name : 'Unknown User',
                    'type' => $notification->type,
                    'notifiable_type' => $notification->notifiable_type,
                    'notifiable_id' => $notification->notifiable_id,
                    'message' => $this->formatNotificationMessage($notification, $fromUser),
                    'created_at' => $notification->created_at,
                ];
            });

        return response()->json($notifications);
    }

    private function formatNotificationMessage($notification, $fromUser)
    {
        $name = $fromUser ? $fromUser->name : 'Unknown User';

        switch ($notification->type) {
            case 'follow':
                return "{$name} Followed you";
            case 'like':
                return "{$name} Liked your post";
            case 'comment':
                return "{$name} Commented on your post";
            case 'job_application':
                return "{$name} Applied to a job";
            case 'job_application_accepted':
                return "{$name} Accepted your application to a job";
            case 'job_application_rejected':
                return "{$name} Rejected your application to a job";
            default:
                return 'You have a new notification';
        }
    }

    public function deleteNotification($id)
    {
        $notification = Notification::where('id', $id)->where('user_id', auth()->id())->first();
        if ($notification) {
            $notification->delete();
            return response()->json(['message' => 'Notification removed']);
        }

        return response()->json(['message' => 'Notification not found'], 404);
    }

    public function toggleFollow(Request $request)
    {
        $authUser = Auth::user();
        $userId = $request->input('user_id');
        $name = $request->input('name');

        $user = User::where('id', $userId)
            ->orWhere('name', $name)
            ->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if ($authUser->id == $user->id) {
            return response()->json(['message' => 'You cannot follow yourself'], 400);
        }

        $existingFollow = Interaction::where([
            'user_id' => $authUser->id,
            'interactionable_type' => User::class,
            'interactionable_id' => $user->id,
            'type' => 'follow',
        ])->first();

        if ($existingFollow) {
            $existingFollow->delete();

            Notification::where([
                'user_id' => $user->id,
                'from_user_id' => $authUser->id,
                'type' => 'follow',
                'notifiable_type' => User::class,
                'notifiable_id' => $user->id,
            ])->delete();

            return response()->json(['message' => 'Unfollowed successfully', 'isFollowed' => false]);
        }

        Interaction::create([
            'user_id' => $authUser->id,
            'interactionable_type' => User::class,
            'interactionable_id' => $user->id,
            'type' => 'follow',
        ]);

        $existingNotification = Notification::where([
            'user_id' => $user->id,
            'from_user_id' => $authUser->id,
            'type' => 'follow',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
        ])->first();

        if (!$existingNotification) {
            Notification::create([
                'user_id' => $user->id,
                'from_user_id' => $authUser->id,
                'type' => 'follow',
                'notifiable_type' => User::class,
                'notifiable_id' => $user->id,
            ]);
        }

        return response()->json(['message' => 'Followed successfully', 'isFollowed' => true]);
    }

    public function getUserAndPosts($name)
    {
        $user = User::where('name', $name)
            ->with('profile.profileImage')
            ->firstOrFail();

        $authUser = Auth::guard('sanctum')->user();
        $user->isFollowed = $authUser
            ? Interaction::where('user_id', $authUser->id)
            ->where('interactionable_id', $user->id)
            ->where('interactionable_type', User::class)
            ->where('type', 'follow')
            ->exists()
            : false;

        $posts = Post::where('user_id', $user->id)
            ->with(['user:id,username,name', 'tags:id,name', 'assets'])
            ->withCount('comments')
            ->get()
            ->map(function ($post) use ($authUser) {
                return [
                    'id' => $post->id,
                    'content' => $post->content,
                    'fullCode' => $post->fullCode,
                    'visibility' => $post->visibility,
                    'created_at' => $post->created_at,
                    'user' => [
                        'id' => $post->user->id,
                        'name' => $post->user->name,
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
                    'isLiked' => $authUser ? Interaction::where('user_id', $authUser->id)
                        ->where('interactionable_id', $post->id)
                        ->where('interactionable_type', Post::class)
                        ->where('type', 'like')
                        ->exists() : false,
                    'isShared' => $authUser ? Interaction::where('user_id', $authUser->id)
                        ->where('interactionable_id', $post->id)
                        ->where('interactionable_type', Post::class)
                        ->where('type', 'share')
                        ->exists() : false,
                    'isBookmarked' => $authUser ? Interaction::where('user_id', $authUser->id)
                        ->where('interactionable_id', $post->id)
                        ->where('interactionable_type', 'post')
                        ->where('type', 'bookmark')
                        ->exists() : false,
                    'isFollowed' => $authUser ? Interaction::where('user_id', $authUser->id)
                        ->where('interactionable_id', $post->user->id)
                        ->where('interactionable_type', User::class)
                        ->where('type', 'follow')
                        ->exists() : false,
                    'assets' => Asset::where('assetable_id', $post->id)
                        ->where('assetable_type', 'App\\Models\\Post')
                        ->get()
                        ->map(fn($asset) => [
                            'id' => $asset->id,
                            'type' => $asset->type,
                            'url' => $asset->getRawOriginal('path'),
                        ]),
                ];
            });

        return response()->json([
            'user' => $user,
            'posts' => $posts,
        ]);
    }
}
