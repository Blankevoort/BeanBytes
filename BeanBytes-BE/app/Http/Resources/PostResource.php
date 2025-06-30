<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Interaction;
use App\Models\Asset;
use App\Models\User;
use App\Models\Post;

class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $authUser = auth('sanctum')->user();

        return [
            'id' => $this->id,
            'content' => $this->content,
            'fullCode' => $this->fullCode,
            'visibility' => $this->visibility,
            'created_at' => $this->created_at,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'username' => $this->user->username,
                'profile_picture' => optional($this->user->profile?->profileImage)->getRawOriginal('path'),
            ],
            'tags' => $this->tags->pluck('name'),
            'likes_count' => Interaction::where('interactionable_id', $this->id)
                ->where('interactionable_type', Post::class)
                ->where('type', 'like')
                ->count(),
            'comments_count' => $this->comments_count ?? 0,
            'shares_count' => Interaction::where('interactionable_id', $this->id)
                ->where('interactionable_type', Post::class)
                ->where('type', 'share')
                ->count(),
            'isLiked' => $authUser ? Interaction::where('user_id', $authUser->id)
                ->where('interactionable_id', $this->id)
                ->where('interactionable_type', Post::class)
                ->where('type', 'like')
                ->exists() : false,
            'isShared' => $authUser ? Interaction::where('user_id', $authUser->id)
                ->where('interactionable_id', $this->id)
                ->where('interactionable_type', Post::class)
                ->where('type', 'share')
                ->exists() : false,
            'isBookmarked' => $authUser ? Interaction::where('user_id', $authUser->id)
                ->where('interactionable_id', $this->id)
                ->where('interactionable_type', Post::class)
                ->where('type', 'bookmark')
                ->exists() : false,
            'isFollowed' => $authUser ? Interaction::where('user_id', $authUser->id)
                ->where('interactionable_id', $this->user->id)
                ->where('interactionable_type', User::class)
                ->where('type', 'follow')
                ->exists() : false,
            'assets' => Asset::where('assetable_id', $this->id)
                ->where('assetable_type', Post::class)
                ->get()
                ->map(fn($asset) => [
                    'id' => $asset->id,
                    'type' => $asset->type,
                    'url' => $asset->getRawOriginal('path'),
                ]),
        ];
    }
}
