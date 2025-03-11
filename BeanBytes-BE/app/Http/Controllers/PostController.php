<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Models\Post;
use App\Models\Asset;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    public function getAllPosts(Request $request)
    {
        $user = Auth::user();

        $posts = Post::with([
            'user:id,username',
            'comments',
            'shares',
            'interactions'
        ])->get()->map(function ($post) use ($user) {
            return [
                'id' => $post->id,
                'content' => $post->content,
                'visibility' => $post->visibility,
                'created_at' => $post->created_at,
                'user' => [
                    'id' => $post->user->id,
                    'username' => $post->user->username,
                ],
                'comments_count' => $post->comments->count(),
                'shares_count' => $post->shares->count(),
                'likes_count' => $post->interactions->where('type', 'like')->count(),
                'isBookmarked' => $user ? $post->interactions()
                    ->where('user_id', $user->id)
                    ->where('type', 'bookmark')
                    ->exists() : false,
            ];
        });

        return response()->json($posts);
    }

    public function createPost(Request $request)
    {
        $request->validate([
            'content' => 'required|string',
            'visibility' => 'required|in:public,private,friends',
            'tags' => 'nullable|array',
            'tags.*' => 'string',
            'assets' => 'nullable|array',
            'assets.*' => 'file|mimes:jpeg,png,jpg,gif,mp4,mov,avi|max:10240',
        ]);

        $post = Post::create([
            'user_id' => Auth::id(),
            'content' => $request->content,
            'visibility' => $request->visibility,
        ]);

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

        if ($request->hasFile('assets')) {
            foreach ($request->file('assets') as $file) {
                $fileType = $file->getMimeType();
                $type = str_contains($fileType, 'image') ? 'image' : 'video';
                $path = $file->store('post_assets', 'public');

                Asset::create([
                    'user_id' => Auth::id(),
                    'assetable_id' => $post->id,
                    'assetable_type' => Post::class,
                    'type' => $type,
                    'path' => $path
                ]);
            }
        }

        return response()->json(['message' => 'Post created', 'post' => $post]);
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

        return response()->json(['message' => 'Post deleted']);
    }
}
