<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Relation::enforceMorphMap([
            'user' => \App\Models\User::class,
            'interaction' => \App\Models\Interaction::class,
            'post' => \App\Models\Post::class,
            'asset' => \App\Models\Asset::class,
            'profile' => \App\Models\Profile::class,
            'job_request' => \App\Models\JobRequest::class,
        ]);
    }
}
