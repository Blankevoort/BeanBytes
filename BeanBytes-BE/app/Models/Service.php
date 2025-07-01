<?php

namespace App\Models;

use App\Models\JobRequest;
use App\Models\CodeSnippet;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'type',
        'status',
    ];

    protected $appends = ['status_label', 'type_label'];

    public function getStatusLabelAttribute()
    {
        return [
            'open' => 'Open',
            'in_progress' => 'In Progress',
            'closed' => 'Closed',
        ][$this->status] ?? $this->status;
    }

    public function getTypeLabelAttribute()
    {
        return [
            'hiring' => 'Hiring',
            'code_snippet' => 'Code Snippet',
            'looking_for_job' => 'Looking For Job',
        ][$this->type] ?? $this->type;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function jobRequest()
    {
        return $this->hasOne(JobRequest::class);
    }
    
    public function codeSnippet()
    {
        return $this->hasOne(CodeSnippet::class);
    }

    public function content()
    {
        return $this->type === 'code_snippet' ? $this->codeSnippet : $this->jobRequest;
    }
}
