<?php

namespace App\Models;

use App\Models\Interaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'content', 'visibility'
    ];

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

    public function shares()
    {
        return $this->hasMany(Share::class);
    }

    public function assets()
    {
        return $this->morphMany(Asset::class, 'assetable');
    }

    public function interactions()
    {
        return $this->morphMany(Interaction::class, 'interactionable');
    }
}
