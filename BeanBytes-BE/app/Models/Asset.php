<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Asset extends Model
{
    use HasFactory;

    protected $fillable = ['assetable_id', 'assetable_type', 'path', 'type'];

    public function assetable(): MorphTo
    {
        return $this->morphTo();
    }
}
