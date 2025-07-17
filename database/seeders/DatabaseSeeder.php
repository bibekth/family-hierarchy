<?php

namespace Database\Seeders;

use App\Models\Hierarchy;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::create([
            'name' => 'Bibek Thapa Magar',
            'email' => 'bibek.thapa0521@gmail.com',
            'password' => Hash::make('monkey@21'),
        ]);

        // for($i=0; $i<100; $i++){
        //     Hierarchy::create(['created_by' => $user->id, 'name' => 'Random '. $i]);
        // }
    }
}
