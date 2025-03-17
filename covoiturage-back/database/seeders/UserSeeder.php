<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       
        
        DB::table('users')->insert([
            'name' => 'amine',
            'email' => 'amineaddem@gmail.com',
            'password' => Hash::make('123456'),
            'role_id' => 2
        ]);
    
    }
}
