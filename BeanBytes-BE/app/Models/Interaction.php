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
        return $this->belongsTo(Post::class, 'interactionable_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function ($interaction) {
            if (in_array($interaction->type, ['like', 'follow', 'comment', 'reply']) && $interaction->to_user_id) {
                Notification::create([
                    'user_id' => $interaction->to_user_id,
                    'notifiable_id' => $interaction->interactionable_id,
                    'notifiable_type' => $interaction->interactionable_type,
                    'type' => $interaction->type,
                ]);
            }
        });
    }
}
