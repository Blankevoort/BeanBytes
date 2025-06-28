<?php

namespace App\Models;

use App\Models\Asset;
use App\Models\Interaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'content', 'fullCode', 'visibility'
    ];

    protected $with = ['assets'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function assets()
    {
        return $this->morphMany(Asset::class, 'assetable', 'assetable_type', 'assetable_id', 'id');
    }

    public function interactions()
    {
        return $this->morphMany(Interaction::class, 'interactionable');
    }

    public function likes(): MorphMany
    {
        return $this->interactions()->where('type', 'like');
    }

    public function shares(): MorphMany
    {
        return $this->interactions()->where('type', 'share');
    }
}
