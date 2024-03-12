<?php
 
namespace Database\Seeders;
 
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
 
class UserSeeder extends Seeder
{
    /**
     * Run the database seeders.
     */

    public function run(): void
    {
        DB::table('users')->insert([
            'email' => 'seeder@email.com',
            'password' => Hash::make('password'),
            'remember_token' => Str::random(60), // Generate a random remember token
            'created_at' => now(), // Set created_at to current time
            'updated_at' => now(), // Set updated_at to current time
        ]);
    }
}