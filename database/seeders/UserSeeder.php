<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $isAdmin = 0;

            if ($i === 1) {
                $isAdmin = 1;
            }

            DB::table('users')->insert([
                'name' => "User" . $i,
                'email' => "email" . $i . '@gmail.com',
                'status' => "Active",
                'password' => Hash::make('123123'),
                'is_admin' => $isAdmin,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
}
