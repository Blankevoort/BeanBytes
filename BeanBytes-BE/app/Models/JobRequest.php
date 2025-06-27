<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'budget',
        'hourly_rate',
        'type',
        'status',
    ];

    protected $appends = ['type_label','status_label'];

    public function getTypeLabelAttribute()
    {
        return [
            'looking_for_job'=>'Looking',
            'hiring'=>'Hiring',
        ][$this->type] ?? $this->type;
    }

    public function getStatusLabelAttribute()
    {
        return [
            'open'=>'Open',
            'in_progress'=>'In Progress',
            'closed'=>'Closed',
        ][$this->status] ?? $this->status;
    }

    public function service()
    {
        return $this->morphOne(Service::class,'details');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function skills()
    {
        return $this->belongsToMany(Skill::class);
    }

    public function interactions()
    {
        return $this->morphMany(Interaction::class,'interactionable');
    }

    public function applications()
    {
        return $this->interactions()->where('type','job_application');
    }

    public function getApplicantsCountAttribute()
    {
        return $this->applications()->count();
    }

}
