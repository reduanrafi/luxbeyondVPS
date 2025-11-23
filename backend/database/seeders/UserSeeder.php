<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin',
                'email' => 'admin@admin.com',
                'password' => bcrypt('password'),
                'role' => 'Admin',
            ],
            [
                'name' => 'Customer',
                'email' => 'customer@customer.com',
                'password' => bcrypt('password'),
                'role' => 'Customer',
            ],
            [
                'name' => 'Traveller',
                'email' => 'traveller@traveller.com',
                'password' => bcrypt('password'),
                'role' => 'Traveller',
            ],
        ];

        foreach ($users as $userData) {
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => $userData['password'],
                ]
            );
            
            // Remove all existing roles and assign the new one
            $user->syncRoles([$userData['role']]);
        }
    }
}
