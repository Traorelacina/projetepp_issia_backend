<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder : CalendrierSeeder
 *
 * Données réelles extraites du document CALENDRIER_SCOLAIRE_2025-26.docx
 */
class CalendrierSeeder extends Seeder
{
    public function run(): void
    {
        $evenements = [
            // ── Vacances ──────────────────────────────────────────────
            [
                'label'         => "Vacances d'octobre",
                'type'          => 'vacances',
                'date_debut'    => '2025-10-18',
                'date_fin'      => '2025-11-02',
                'annee_scolaire'=> '2025-2026',
                'couleur'       => '#1565c0',
            ],
            [
                'label'         => 'Vacances de décembre',
                'type'          => 'vacances',
                'date_debut'    => '2025-12-20',
                'date_fin'      => '2026-01-04',
                'annee_scolaire'=> '2025-2026',
                'couleur'       => '#1565c0',
            ],
            [
                'label'         => 'Vacances de février',
                'type'          => 'vacances',
                'date_debut'    => '2026-02-21',
                'date_fin'      => '2026-03-08',
                'annee_scolaire'=> '2025-2026',
                'couleur'       => '#1565c0',
            ],
            [
                'label'         => "Vacances d'avril",
                'type'          => 'vacances',
                'date_debut'    => '2026-04-18',
                'date_fin'      => '2026-05-03',
                'annee_scolaire'=> '2025-2026',
                'couleur'       => '#1565c0',
            ],
            [
                'label'         => "Vacances d'été",
                'type'          => 'vacances',
                'date_debut'    => '2026-06-26',
                'date_fin'      => '2026-09-30',
                'annee_scolaire'=> '2025-2026',
                'couleur'       => '#1565c0',
            ],

            // ── Rentrée ───────────────────────────────────────────────
            [
                'label'         => 'Rentrée scolaire 2025-2026',
                'type'          => 'rentree',
                'date_debut'    => '2025-10-06',
                'date_fin'      => '2025-10-06',
                'annee_scolaire'=> '2025-2026',
                'couleur'       => '#1B7A3E',
            ],

            // ── Jours fériés ──────────────────────────────────────────
            [
                'label'         => 'Maouloud',
                'type'          => 'ferie',
                'date_debut'    => '2025-09-04',
                'date_fin'      => '2025-09-04',
                'annee_scolaire'=> '2025-2026',
                'couleur'       => '#c62828',
            ],
            [
                'label'         => 'Journée de la Paix',
                'type'          => 'ferie',
                'date_debut'    => '2025-11-15',
                'date_fin'      => '2025-11-15',
                'annee_scolaire'=> '2025-2026',
                'couleur'       => '#c62828',
            ],
            [
                'label'         => 'Aïd el-Fitr',
                'type'          => 'ferie',
                'date_debut'    => '2026-03-20',
                'date_fin'      => '2026-03-20',
                'annee_scolaire'=> '2025-2026',
                'couleur'       => '#c62828',
            ],
            [
                'label'         => 'Lundi de Pâques',
                'type'          => 'ferie',
                'date_debut'    => '2026-04-06',
                'date_fin'      => '2026-04-06',
                'annee_scolaire'=> '2025-2026',
                'couleur'       => '#c62828',
            ],
            [
                'label'         => "Jeudi de l'Ascension",
                'type'          => 'ferie',
                'date_debut'    => '2026-05-14',
                'date_fin'      => '2026-05-14',
                'annee_scolaire'=> '2025-2026',
                'couleur'       => '#c62828',
            ],
            [
                'label'         => 'Lundi de Pentecôte',
                'type'          => 'ferie',
                'date_debut'    => '2026-05-25',
                'date_fin'      => '2026-05-25',
                'annee_scolaire'=> '2025-2026',
                'couleur'       => '#c62828',
            ],
            [
                'label'         => 'Tabaski (Aïd el-Adha)',
                'type'          => 'ferie',
                'date_debut'    => '2026-05-27',
                'date_fin'      => '2026-05-27',
                'annee_scolaire'=> '2025-2026',
                'couleur'       => '#c62828',
            ],
        ];

        foreach ($evenements as $evt) {
            DB::table('calendrier')->insertOrIgnore(array_merge($evt, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
