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
        $user = User::firstOrCreate([
            'email' => 'testuser@example.com'
        ], [
            'name' => 'Test User',
            'username' => 'testuser',
            'phone' => '09123456789',
            'password' => bcrypt('password'),
        ]);

        $jobTitles = [
            'Web Developer Needed',
            'Graphic Designer Wanted',
            'SEO Expert Required',
            'React Developer for Startup',
            'Laravel Developer for eCommerce'
        ];

        $lookingForJobTitles = [
            'Junior Developer Seeking Work',
            'Experienced UI/UX Designer Available',
            'Full-Stack Developer Looking for Projects',
            'Freelance Writer Open for Work',
            'Mobile App Developer Ready for Hire'
        ];

        foreach ($jobTitles as $title) {
            $jobRequest = JobRequest::create([
                'budget' => rand(5000, 25000),
                'hourly_rate' => rand(25, 150),
                'applicants_count' => 0,
            ]);

            Service::create([
                'title' => $title,
                'description' => "We are looking for a skilled professional to handle " . Str::lower($title) . ".",
                'type' => 'hiring',
                'status' => 'open',
                'user_id' => $user->id,
                'details_id' => $jobRequest->id,
                'details_type' => JobRequest::class,
            ]);

            $randomSkills = Skill::inRandomOrder()->limit(rand(2, 5))->pluck('id');
            $jobRequest->skills()->attach($randomSkills);
        }

        foreach ($lookingForJobTitles as $title) {
            $jobRequest = JobRequest::create([
                'budget' => rand(1000, 7000),
                'hourly_rate' => rand(15, 75),
                'applicants_count' => 0,
            ]);

            Service::create([
                'title' => $title,
                'description' => "I am an experienced professional with expertise in " . Str::lower($title) . ". Looking for freelance or full-time opportunities.",
                'type' => 'looking_for_job',
                'status' => 'open',
                'user_id' => $user->id,
                'details_id' => $jobRequest->id,
                'details_type' => JobRequest::class,
            ]);

            $randomSkills = Skill::inRandomOrder()->limit(rand(1, 4))->pluck('id');
            $jobRequest->skills()->attach($randomSkills);
        }

        $snippets = [
            [
                'title' => 'Laravel API Response Helper',
                'description' => 'A helper function for standardized API responses in Laravel.',
                'language' => 'PHP',
                'license' => 'MIT',
                'file_path' => 'snippets/api_response.php',
                'is_free' => true
            ],
            [
                'title' => 'Debounce Function in JavaScript',
                'description' => 'A simple debounce function to optimize event handling.',
                'language' => 'JavaScript',
                'license' => 'MIT',
                'file_path' => 'snippets/debounce.js',
                'is_free' => true
            ],
            [
                'title' => 'Tailwind Flexbox Centering',
                'description' => 'CSS utility class for centering elements using Flexbox.',
                'language' => 'CSS',
                'license' => 'MIT',
                'file_path' => 'snippets/flex-center.css',
                'is_free' => true
            ],
            [
                'title' => 'Python Fibonacci Generator',
                'description' => 'A generator function that yields Fibonacci numbers.',
                'language' => 'Python',
                'license' => 'MIT',
                'file_path' => 'snippets/fibonacci.py',
                'is_free' => false
            ],
            [
                'title' => 'Quasar Dark Mode Toggle',
                'description' => 'A Quasar function to toggle dark mode dynamically.',
                'language' => 'JavaScript',
                'license' => 'MIT',
                'file_path' => 'snippets/dark-mode.js',
                'is_free' => true
            ]
        ];

        foreach ($snippets as $snippetData) {
            $snippet = CodeSnippet::create([
                'language' => $snippetData['language'],
                'license' => $snippetData['license'],
                'file_path' => $snippetData['file_path'],
                'is_free' => $snippetData['is_free'],
            ]);

            Service::create([
                'title' => $snippetData['title'],
                'description' => $snippetData['description'],
                'type' => 'code_snippet',
                'status' => 'published',
                'user_id' => $user->id,
                'details_id' => $snippet->id,
                'details_type' => CodeSnippet::class,
            ]);

            $randomTags = Tag::inRandomOrder()->limit(rand(1, 3))->pluck('id');
            $snippet->tags()->attach($randomTags);
        }
    }
}
