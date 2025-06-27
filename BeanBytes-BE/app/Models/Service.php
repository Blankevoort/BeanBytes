<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
      'title',
      'description',
      'type',    
      'status',
      'details_id',
      'details_type',
    ];

    public function details()
    {
        return $this->morphTo();
    }
}
