<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\Tag;
use App\Models\User;
use App\Models\Skill;
use App\Models\JobRequest;
use App\Models\CodeSnippet;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run()
    {
        $user = User::firstOrCreate(
            ['email'=>'testuser@example.com'],
            ['name'=>'Test User','username'=>'testuser','phone'=>'09123456789','password'=>bcrypt('password')]
        );

        $jobTitles = [
            'Web Developer',
        ];
        
        foreach ($jobTitles as $title) {
            $job = JobRequest::create([
                'user_id'=>$user->id,
                'budget'=>rand(5000,25000),
                'hourly_rate'=>rand(25,150),
                'type'=>'hiring',
                'status'=>'open',
            ]);

            Service::create([
                'title'=>$title,
                'description'=>"We are looking for ".Str::lower($title),
                'type'=>'hiring',
                'status'=>'open',
                'details_id'=>$job->id,
                'details_type'=>JobRequest::class,
            ]);

            $job->skills()->attach(
                Skill::inRandomOrder()->limit(rand(2,5))->pluck('id')
            );
        }
    }
}
