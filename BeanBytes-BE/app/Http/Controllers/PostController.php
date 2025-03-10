<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Tag;
use App\Models\Asset;
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
                'isBookmarked' => $post->interactions
                    ->where('type', 'bookmark')
                    ->where('user_id', $user->id)
                    ->isNotEmpty(), // Check if the authenticated user bookmarked the post
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
            'tags.*' => 'string'
        ]);

        $post = Post::create([
            'user_id' => Auth::id(),
            'content' => $request->content,
            'visibility' => $request->visibility,
        ]);

        if ($request->has('tags')) {
            $tagIds = [];
            foreach ($request->tags as $tagName) {
                $tag = Tag::firstOrCreate(['name' => $tagName]);
                $tagIds[] = $tag->id;
            }
            $post->tags()->sync($tagIds);
        }

        return response()->json(['message' => 'Post created', 'post' => $post]);
    }

    public function editPost(Request $request, Post $post)
    {
        $this->authorize('update', $post);

        $request->validate([
            'content' => 'nullable|string',
            'visibility' => 'nullable|in:public,private,friends',
        ]);

        $post->update($request->only(['content', 'visibility']));

        return response()->json(['message' => 'Post updated', 'post' => $post]);
    }

    public function deletePost(Post $post)
    {
        $this->authorize('delete', $post);

        $post->delete();

        return response()->json(['message' => 'Post deleted']);
    }

    public function uploadAsset(Request $request, Post $post)
    {
        $this->authorize('update', $post);

        $request->validate([
            'file' => 'required|file|mimes:jpeg,png,jpg,gif,mp4,mov,avi|max:10240',
        ]);

        $fileType = $request->file->getMimeType();
        $type = str_contains($fileType, 'image') ? 'image' : 'video';
        $path = $request->file->store('post_assets', 'public');

        $asset = Asset::create([
            'user_id' => Auth::id(),
            'assetable_id' => $post->id,
            'assetable_type' => Post::class,
            'type' => $type,
            'path' => $path
        ]);

        return response()->json(['message' => 'Asset uploaded', 'asset' => $asset]);
    }
}
