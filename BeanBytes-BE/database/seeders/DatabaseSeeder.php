<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // User::factory(10)->create();

        User::create([
            'name' => 'Moein_Sedaqati',
            'username' => 'Moein',
            'email' => 'moeensedaghaty86@gmail.com',
            'phone' => '09379608155',
            'password' => Hash::make('Moeen576786'),
        ]);

        User::create([
            'name' => 'aylar_jorjany',
            'username' => 'Aylar',
            'email' => 'moeensedaghaty71@gmail.com',
            'phone' => '09119669692',
            'password' => Hash::make('Moeen576786'),
        ]);

        $this->call([
            SkillSeeder::class,
            JobRequestSeeder::class,
        ]);
    }
}
