<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Seeder : RolesPermissionsSeeder
 *
 * Crée les rôles et permissions utilisés dans les controllers :
 *  - ActualiteController : create-actualite, edit-actualite, delete-actualite, publish-actualite
 *  - UserController      : syncRoles(['super-admin','directeur','secretaire','enseignant'])
 */
class RolesPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Réinitialiser le cache Spatie
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ── Permissions ───────────────────────────────────────────────
        $permissions = [
            // Actualités
            'create-actualite',
            'edit-actualite',
            'delete-actualite',
            'publish-actualite',

            // Activités
            'create-activite',
            'edit-activite',
            'delete-activite',

            // Inscriptions
            'view-inscriptions',
            'update-inscription-statut',
            'export-inscriptions',

            // Messages
            'view-messages',
            'delete-messages',

            // Calendrier
            'manage-calendrier',

            // Médias
            'upload-media',
            'delete-media',

            // Paramètres
            'manage-parametres',

            // Utilisateurs (super-admin uniquement)
            'manage-users',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'sanctum']);
        }

        // ── Rôles ─────────────────────────────────────────────────────

        // super-admin : toutes les permissions
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'sanctum']);
        $superAdmin->syncPermissions($permissions);

        // directeur : tout sauf gestion des utilisateurs
        $directeur = Role::firstOrCreate(['name' => 'directeur', 'guard_name' => 'sanctum']);
        $directeur->syncPermissions(array_filter($permissions, fn ($p) => $p !== 'manage-users'));

        // secrétaire : inscriptions, messages, calendrier, médias
        $secretaire = Role::firstOrCreate(['name' => 'secretaire', 'guard_name' => 'sanctum']);
        $secretaire->syncPermissions([
            'view-inscriptions',
            'update-inscription-statut',
            'export-inscriptions',
            'view-messages',
            'manage-calendrier',
            'upload-media',
        ]);

        // enseignant : lecture activités, upload médias
        $enseignant = Role::firstOrCreate(['name' => 'enseignant', 'guard_name' => 'sanctum']);
        $enseignant->syncPermissions([
            'create-activite',
            'edit-activite',
            'upload-media',
        ]);
    }
}
