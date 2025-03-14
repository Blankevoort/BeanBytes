<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Models\Post;
use App\Models\Asset;
use App\Models\Interaction;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    public function getAllPosts()
    {
        $user = Auth::check() ? Auth::user() : null;

        $posts = Post::with([
            'user:id,username',
            'comments',
            'shares',
            'interactions',
            'tags:id,name',
        ])->get()->map(function ($post) use ($user) {
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
                'comments_count' => $post->comments->count(),
                'shares_count' => $post->shares->count(),
                'likes_count' => $post->interactions->where('type', 'like')->count(),
                'isBookmarked' => $user ? Interaction::where('user_id', $user->id)
                    ->where('interactionable_id', $post->id)
                    ->where('interactionable_type', 'post')
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
            'visibility' => 'nullable|in:public,private,friends',
            'tags' => 'nullable|array',
            'tags.*' => 'string',
            'assets' => 'nullable|array',
            'assets.*' => 'file|mimes:jpeg,png,jpg,gif,mp4,mov,avi|max:10240',
        ]);

        preg_match('/`(.*?)`/s', $request->content, $codeMatch);
        $fullCode = $codeMatch[1] ?? null;

        $cleanContent = trim(preg_replace('/`(.*?)`/s', '', $request->content));

        preg_match_all('/#([\p{L}0-9_]+)/u', $request->content, $matches);
        $hashtags = $matches[1] ?? [];

        $cleanedContent = preg_replace('/#([\p{L}0-9_]+)/u', '', $request->content);
        $cleanedContent = trim(preg_replace('/\s+/', ' ', $cleanedContent));

        $allTags = array_unique(array_merge($hashtags, $request->tags ?? []));

        $post = Post::create([
            'user_id' => Auth::id(),
            'content' => $cleanContent,
            'fullCode' => $fullCode,
            'visibility' => $request->input('visibility', 'private'),
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

        return response()->json([
            'message' => 'Post created',
            'post' => $post
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

        return response()->json(['message' => 'Post deleted']);
    }

    public function getRandomTags()
    {
        $tags = Tag::inRandomOrder()->limit(8)->get(['name']);
        return response()->json($tags);
    }
}
