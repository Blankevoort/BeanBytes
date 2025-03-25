<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'bio', 'job_title', 'location', 'profile_image'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function profileImage(): MorphOne
    {
        return $this->morphOne(Asset::class, 'assetable');
    }
}
