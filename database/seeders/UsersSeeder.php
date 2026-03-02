<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        // Définition des utilisateurs à créer
        $users = [
            [
                'name'     => 'Administrateur Principal',
                'email'    => 'adminprin@cppe-issia.ci',
                'password' => 'Admin@CPPE2025!',
                'role'     => 'super-admin',
            ],
            [
                'name'     => 'Directeur Issia',
                'email'    => 'directeur@cppe-issia.ci',
                'password' => 'Directeur@2025!',
                'role'     => 'directeur',
            ],
            [
                'name'     => 'Secrétaire Générale',
                'email'    => 'secretaire@cppe-issia.ci',
                'password' => 'Secretaire@2025!',
                'role'     => 'secretaire',
            ],
            [
                'name'     => 'Enseignant 1',
                'email'    => 'enseignant1@cppe-issia.ci',
                'password' => 'Enseignant@2025!',
                'role'     => 'enseignant',
            ],
            [
                'name'     => 'Enseignant 2',
                'email'    => 'enseignant2@cppe-issia.ci',
                'password' => 'Enseignant@2025!',
                'role'     => 'enseignant',
            ],
        ];

        foreach ($users as $userData) {
            // Créer ou récupérer l'utilisateur (évite les doublons)
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name'     => $userData['name'],
                    'password' => Hash::make($userData['password']),
                    'actif'    => true,
                ]
            );

            // Récupérer le rôle avec guard 'sanctum' (existant dans votre table)
            $role = Role::where('name', $userData['role'])
                        ->where('guard_name', 'sanctum')
                        ->first();

            if ($role) {
                // Assigner le rôle à l'utilisateur
                $user->assignRole($role);
            } else {
                $this->command->warn("⚠ Rôle '{$userData['role']}' avec guard 'sanctum' introuvable.");
            }
        }

        $this->command->info('✅ Utilisateurs créés avec succès.');
    }
}