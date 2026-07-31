<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'JJ Miranda',
                'username' => 'jj.miranda',
                'email' => 'jj.miranda@ambatugrow.test',
                'role' => 'manager',
                'job_title' => 'Manager',
                'department' => 'Marketing',
                'avatar_initial' => 'JJ',
            ],
            [
                'name' => 'Sarah Jerkins',
                'username' => 'sarah.jerkins',
                'email' => 'sarah.jerkins@ambatugrow.test',
                'role' => 'manager',
                'job_title' => 'Manager',
                'department' => 'Marketing',
                'avatar_initial' => 'SJ',
            ],
            [
                'name' => 'Johny Papa',
                'username' => 'johny.papa',
                'email' => 'johny.papa@ambatugrow.test',
                'role' => 'department_head',
                'job_title' => 'Director of Marketing',
                'department' => 'Marketing',
                'avatar_initial' => 'JP',
            ],
            [
                'name' => 'Emily Johnson',
                'username' => 'emily.johnson',
                'email' => 'emily.johnson@ambatugrow.test',
                'role' => 'manager',
                'job_title' => 'Marketing Specialist',
                'department' => 'Marketing',
                'avatar_initial' => 'EJ',
            ],
            [
                'name' => 'Michael Finn',
                'username' => 'finance.manager',
                'email' => 'finance.manager@ambatugrow.test',
                'role' => 'finance_manager',
                'job_title' => 'Finance Manager',
                'department' => 'Finance',
                'avatar_initial' => 'MF',
            ],
            [
                'name' => 'System Admin',
                'username' => 'system.admin',
                'email' => 'admin@ambatugrow.test',
                'role' => 'admin',
                'job_title' => 'System Administrator',
                'department' => 'IT',
                'avatar_initial' => 'SA',
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['username' => $user['username']],
                array_merge($user, ['password' => Hash::make('password')])
            );
        }

        User::where('username', 'robin.lapa')->orWhere('name', 'Robin Lapa')->orWhere('username', 'sarah.jenkins')->orWhere('name', 'Sarah Jenkins')->delete();
    }
}
