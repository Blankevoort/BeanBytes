<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CodeSnippet extends Model
{
    protected $fillable = ['language', 'license', 'file_path', 'is_free'];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
