<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
        ]);

        $adminRoleId = Role::where('slug', 'admin')->value('id');
        $userRoleId = Role::where('slug', 'user')->value('id');

        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'SafeGive Admin',
                'password' => 'password',
                'role_id' => $adminRoleId,
            ],
        );

        User::updateOrCreate(
            ['email' => 'wahyu@example.com'],
            [
                'name' => 'Wahyu S Tamuu',
                'password' => 'password',
                'role_id' => $userRoleId,
            ],
        );
    }
}
