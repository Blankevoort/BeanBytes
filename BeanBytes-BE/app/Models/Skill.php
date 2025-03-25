<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public $timestamps = false;

    public function jobRequests()
    {
        return $this->belongsToMany(JobRequest::class, 'job_request_skill');
    }
}
