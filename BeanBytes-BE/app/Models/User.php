<?php

namespace App\Models;

use App\Models\Profile;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = ['name', 'username', 'email', 'phone', 'password'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            $randomNumber = rand(10000, 99999);

            $user->name = $user->name ?? "User{$randomNumber} Name";
            $user->username = $user->username ?? "User{$randomNumber} Username";
        });
    }

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function interactions()
    {
        return $this->hasMany(Interaction::class);
    }
}
