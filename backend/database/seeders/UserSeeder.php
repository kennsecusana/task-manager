<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Matt',
            'email' => 'matt@goteam.com',
            'password' => Hash::make('password'),
            'image_url' => 'https://ui-avatars.com/api/?name=Matt&background=random',
        ]);
    }
}
