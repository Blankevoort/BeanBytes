<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Interaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'interactionable_id',
        'interactionable_type',
        'type',
        'shared_post_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function interactionable()
    {
        return $this->morphTo();
    }

    public function sharedPost()
    {
        return $this->belongsTo(Post::class, 'shared_post_id');
    }
}
