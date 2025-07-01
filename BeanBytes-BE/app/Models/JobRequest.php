<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'budget',
        'hourly_rate',
    ];

    public function skills()
    {
        return $this->belongsToMany(Skill::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function interactions()
    {
        return $this->morphMany(Interaction::class, 'interactionable');
    }

    public function applications()
    {
        return $this->interactions()->where('type', 'job_application');
    }

    public function getApplicantsCountAttribute()
    {
        return $this->applications()->count();
    }
}
