<?php

namespace Database\Seeders;

use App\Models\JobRequest;
use App\Models\User;
use App\Models\Skill;
use Illuminate\Database\Seeder;

class JobRequestSeeder extends Seeder
{
    public function run()
    {
        $user = User::first();

        if (!$user) {
            $user = User::create([
                'name' => 'Test User',
                'username' => 'testuser',
                'email' => 'testuser@example.com',
                'phone' => '09123456789',
                'password' => bcrypt('password'),
            ]);
        }

        for ($i = 1; $i <= 5; $i++) {
            $jobRequest = JobRequest::create([
                'title' => "Job Request #$i",
                'description' => "Description for job request #$i.",
                'type' => ['looking_for_job', 'hiring'][array_rand(['looking_for_job', 'hiring'])],
                'budget' => rand(1000, 10000),
                'hourly_rate' => rand(10, 100),
                'status' => ['open', 'in_progress', 'closed'][array_rand(['open', 'in_progress', 'closed'])],
                'user_id' => $user->id,
            ]);

            $randomSkills = Skill::inRandomOrder()->limit(rand(1, 5))->pluck('id');
            $jobRequest->skills()->attach($randomSkills);
        }
    }
}
