<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Models\Post;
use App\Models\User;
use App\Models\Asset;
use App\Models\Comment;
use App\Models\Interaction;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\PostResource;

class PostController extends Controller
{
    public function getAllPosts()
    {
        $posts = Post::with([
            'user:id,username,name',
            'user.profile.profileImage',
            'tags:id,name',
            'assets'
        ])
        ->withCount('comments')
        ->get();

        return PostResource::collection($posts);
    }

    public function createPost(Request $request)
    {
        $request->validate([
            'content' => 'required|string',
            'visibility' => 'nullable|in:public,private,friends',
            'tags' => 'nullable|array',
            'tags.*' => 'string',
            'assets' => 'nullable|file|mimes:jpeg,png,jpg|max:10240',
        ]);

        preg_match('/`(.*?)`/s', $request->content, $codeMatch);
        $fullCode = $codeMatch[1] ?? null;

        $cleanContent = trim(preg_replace('/`(.*?)`/s', '', $request->content));

        preg_match_all('/#([\p{L}0-9_]+)/u', $request->content, $matches);
        $hashtags = $matches[1] ?? [];

        $cleanedContent = preg_replace('/#([\p{L}0-9_]+)/u', '', $request->content);
        $cleanedContent = nl2br(trim($cleanedContent));

        $allTags = array_unique(array_merge($hashtags, $request->tags ?? []));

        $post = Post::create([
            'user_id'    => Auth::id(),
            'content'    => $cleanContent,
            'fullCode'   => $fullCode,
            'visibility' => $request->input('visibility', 'public'),
        ]);

        if (!empty($allTags)) {
            $tagIds = [];
            foreach ($allTags as $tagName) {
                $tag = Tag::firstOrCreate(
                    ['name' => $tagName],
                    ['slug' => Str::slug($tagName)]
                );
                $tagIds[] = $tag->id;
            }
            $post->tags()->sync($tagIds);
        }

        if ($request->hasFile('assets')) {
            $file = $request->file('assets');
            $mime = $file->getMimeType();
            $type = str_contains($mime, 'image') ? 'image' : 'video';
            $path = $file->store('post_assets', 'public');

            Asset::create([
                'user_id'        => Auth::id(),
                'assetable_id'   => $post->id,
                'assetable_type' => Post::class,
                'type'           => $type,
                'path'           => $path,
            ]);
        }

        return response()->json([
            'status' => 200,
        ]);
    }

    public function editPost(Request $request, Post $post)
    {
        if (Auth::id() !== $post->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'content' => 'nullable|string',
            'visibility' => 'nullable|in:public,private,friends',
            'tags' => 'nullable|array',
            'tags.*' => 'string',
        ]);

        $post->update($request->only(['content', 'visibility']));

        if ($request->has('tags')) {
            $tagIds = [];
            foreach ($request->tags as $tagName) {
                $tag = Tag::firstOrCreate(
                    ['name' => $tagName],
                    ['slug' => Str::slug($tagName)]
                );
                $tagIds[] = $tag->id;
            }
            $post->tags()->sync($tagIds);
        }

        return response()->json(['message' => 'Post updated', 'post' => $post]);
    }

    public function deletePost(Post $post)
    {
        if (Auth::id() !== $post->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $post->delete();

        return response()->json(['status' => 200]);
    }

    public function getPost($id)
    {
        $post = Post::with([
            'user:id,username,name',
            'user.profile.profileImage',
            'tags:id,name',
            'assets'
        ])
        ->withCount('comments')
        ->findOrFail($id);

        return new PostResource($post);
    }

    public function getPostComments(Request $request)
    {
        $request->validate([
            'post_id' => 'required|exists:posts,id'
        ]);

        $comments = Comment::where('post_id', $request->post_id)
            ->with(['user:id,username'])
            ->latest()
            ->get();

        return response()->json([
            'comments' => $comments
        ]);
    }

    public function getTrendingTags()
    {
        $tags = Tag::withCount('posts')
            ->orderByDesc('posts_count')
            ->limit(8)
            ->pluck('name');

        return response()->json($tags);
    }

    public function search($value = null)
    {
        if (!$value) {
            return response()->json([
                'tag' => [],
                'user' => []
            ]);
        }

        $tags = Tag::where('name', 'LIKE', "%{$value}%")
            ->pluck('name');

        $users = User::where('username', 'LIKE', "%{$value}%")
            ->select('name', 'username')
            ->get();

        return response()->json([
            'tag' => $tags,
            'user' => $users
        ]);
    }

    public function getFollowingPosts()
    {
        $authUser = Auth::guard('sanctum')->user();

        $followingIds = Interaction::where([
            ['user_id', $authUser->id],
            ['interactionable_type', User::class],
            ['type', 'follow']
        ])->pluck('interactionable_id');

        $posts = Post::with([
            'user:id,username,name',
            'user.profile.profileImage',
            'tags:id,name',
            'assets'
        ])
            ->whereIn('user_id', $followingIds)
            ->orderBy('created_at', 'desc')
            ->withCount('comments')
            ->get();

        return PostResource::collection($posts);
    }

    public function getTrendingPosts()
    {
        $posts = Post::with([
            'user:id,username,name',
            'user.profile.profileImage',
            'tags:id,name',
            'assets'
        ])
            ->withCount('comments')
            ->get()
            ->sortByDesc('likes_count')
            ->sortByDesc('comments_count')
            ->sortByDesc('shares_count')
            ->values()
            ->take(20);

        return PostResource::collection($posts);
    }
}
