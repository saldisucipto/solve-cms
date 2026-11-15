<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = Role::where('name', 'super-admin')->firstOrFail();
        $editor = Role::where('name', 'editor')->firstOrFail();
        $agent = Role::where('name', 'support-agent')->firstOrFail();

        $admin = User::updateOrCreate(
            ['email' => 'admin@solvecms.test'],
            [
                'name' => 'Solve Administrator',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'status' => 'active',
            ],
        );

        $admin->roles()->syncWithoutDetaching([$superAdmin->id]);

        $editorUser = User::updateOrCreate(
            ['email' => 'editor@solvecms.test'],
            [
                'name' => 'Solve Editor',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'status' => 'active',
            ],
        );

        $editorUser->roles()->syncWithoutDetaching([$editor->id]);

        $agentUser = User::updateOrCreate(
            ['email' => 'agent@solvecms.test'],
            [
                'name' => 'Solve Support Agent',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'status' => 'active',
            ],
        );

        $agentUser->roles()->syncWithoutDetaching([$agent->id]);
    }
}
