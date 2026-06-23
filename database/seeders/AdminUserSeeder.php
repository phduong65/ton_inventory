<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Admin',      'email' => 'admin@inventory.local',      'role' => 'admin'],
            ['name' => 'Quản lý',    'email' => 'manager@inventory.local',    'role' => 'manager'],
            ['name' => 'Kế toán kho','email' => 'accountant@inventory.local', 'role' => 'accountant'],
            ['name' => 'Giám sát',   'email' => 'supervisor@inventory.local', 'role' => 'supervisor'],
        ];

        foreach ($users as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'     => $data['name'],
                    'password' => bcrypt('password'),
                    'theme'    => 'light',
                ]
            );
            $user->syncRoles([$data['role']]);
        }
    }
}
