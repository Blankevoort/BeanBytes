<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Skill;
use App\Models\Service;
use App\Models\JobRequest;
use App\Models\CodeSnippet;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run()
    {
        $user = User::firstOrCreate(
            ['email' => 'testuser@example.com'],
            [
                'name'     => 'Test User',
                'username' => 'testuser',
                'phone'    => '09123456789',
                'password' => bcrypt('password'),
            ]
        );

        $jobTitles = ['Web Developer', 'Mobile App Designer', 'Laravel Expert'];

        foreach ($jobTitles as $title) {
            $service = Service::create([
                'user_id'     => $user->id,
                'title'       => $title,
                'description' => "We are looking for a " . Str::lower($title),
                'type'        => 'hiring',
                'status'      => 'open',
            ]);

            $job = JobRequest::create([
                'service_id'  => $service->id,
                'budget'      => rand(5000, 25000),
                'hourly_rate' => rand(25, 150),
            ]);

            $job->skills()->attach(
                Skill::inRandomOrder()->limit(rand(2, 5))->pluck('id')
            );
        }

        $languages = ['PHP', 'JavaScript', 'Python', 'Go'];

        foreach ($languages as $lang) {
            $service = Service::create([
                'user_id'     => $user->id,
                'title'       => $lang . ' Helper Functions',
                'description' => "Reusable functions for " . $lang,
                'type'        => 'code_snippet',
                'status'      => 'open',
            ]);

            CodeSnippet::create([
                'service_id' => $service->id,
                'language'   => $lang,
                'license'    => 'MIT',
                'file_path'  => "snippets/" . strtolower($lang) . "_helper.php",
                'is_free'    => rand(0, 1),
            ]);
        }
    }
}
