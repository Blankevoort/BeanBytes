<?php

namespace Database\Seeders;

use App\Models\Skill;
use Illuminate\Database\Seeder;

class SkillSeeder extends Seeder
{
    public function run()
    {
        $skills = ['PHP', 'Laravel', 'JavaScript', 'Vue.js', 'React', 'Python', 'Django', 'SQL', 'Node.js', 'C++'];

        foreach ($skills as $skill) {
            Skill::create(['name' => $skill]);
        }
    }
}
