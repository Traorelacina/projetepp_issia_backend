<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder : ParametresSeeder
 *
 * Initialise toutes les clés définies dans Public\ParametreController::CLES_PUBLIQUES
 * + les clés admin supplémentaires.
 */
class ParametresSeeder extends Seeder
{
    public function run(): void
    {
        $parametres = [
            // ── Établissement ─────────────────────────────────────────
            ['cle' => 'nom_etablissement',       'valeur' => "Centre de Protection de la Petite Enfance d'Issia"],
            ['cle' => 'slogan',                  'valeur' => "L'épanouissement de l'enfant, notre priorité"],

            // ── Directeur ─────────────────────────────────────────────
            ['cle' => 'nom_directeur',           'valeur' => null],
            ['cle' => 'photo_directeur',         'valeur' => null],
            ['cle' => 'mot_directeur',           'valeur' => null],

            // ── Contact ───────────────────────────────────────────────
            ['cle' => 'telephone',               'valeur' => '07 07 18 65 59'],
            ['cle' => 'telephone_2',             'valeur' => '05 06 48 22 01'],
            ['cle' => 'email',                   'valeur' => 'direction@cppe-issia.ci'],
            ['cle' => 'adresse',                 'valeur' => "Complexe Socio-Éducatif d'Issia, Haut-Sassandra"],
            ['cle' => 'horaires',                'valeur' => 'Lundi au vendredi, 7h30 à 16h30'],

            // ── Scolarité ─────────────────────────────────────────────
            ['cle' => 'annee_scolaire_courante', 'valeur' => '2025-2026'],
            ['cle' => 'date_rentree',            'valeur' => '06 octobre 2025'],
            ['cle' => 'scolarite_montant',       'valeur' => '50000'],

            // ── Inscriptions ──────────────────────────────────────────
            ['cle' => 'inscriptions_ouvertes',   'valeur' => 'true'],
        ];

        foreach ($parametres as $p) {
            DB::table('parametres')->updateOrInsert(
                ['cle' => $p['cle']],
                ['cle' => $p['cle'], 'valeur' => $p['valeur'], 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
