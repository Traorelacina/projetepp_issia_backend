<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Créer le rôle super-admin s'il n'existe pas
        Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);

        $admin = User::firstOrCreate(
            ['email' => 'admin@cppe-issia.ci'],
            [
                'name'     => 'Administrateur CPPE',
                'password' => Hash::make('Admin@CPPE2025!'),
                'actif'    => true,
            ]
        );

        $admin->assignRole('super-admin');

        $this->command->info("Super-admin créé : admin@cppe-issia.ci / Admin@CPPE2025!");
        $this->command->warn("⚠  Changez le mot de passe après la première connexion !");
    }
}