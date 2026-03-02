<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesPermissionsSeeder::class, // Doit tourner avant AdminSeeder
            AdminSeeder::class,
            ParametresSeeder::class,
            CalendrierSeeder::class,
            UsersSeeder::class,
        ]);
    }
}
