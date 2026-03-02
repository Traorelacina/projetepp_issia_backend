<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Table : calendrier
 *
 * Champs déduits de :
 *  - Admin\CalendrierController  : validate() store/update
 *  - Public\CalendrierController : parAnnee(), orderBy('date_debut')
 *
 * Particularités :
 *  - couleur : champ hexadécimal (#RRGGBB) pour colorisation frontend
 *  - date_fin : required + after_or_equal:date_debut (un jour férié = date_debut == date_fin)
 *  - Scopes : parAnnee($annee), parType($type)
 *  - config('cppe.annee_scolaire') par défaut = '2025-2026'
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendrier', function (Blueprint $table) {
            $table->id();

            // Intitulé (ex: Vacances d'octobre, Maouloud, Rentrée…)
            $table->string('label');

            // Description longue (optionnelle)
            $table->text('description')->nullable();

            // Dates — date_fin required (après_ou_égal date_debut)
            $table->date('date_debut');
            $table->date('date_fin');

            // Type — 5 valeurs issues du validate()
            $table->enum('type', [
                'vacances',
                'ferie',
                'rentree',
                'evenement',
                'examen',
            ])->default('evenement');

            // Année scolaire (ex: '2025-2026')
            $table->string('annee_scolaire', 10)->default('2025-2026');

            // Couleur hexadécimale pour le frontend (#1B7A3E, #F5A623…)
            $table->string('couleur', 7)->nullable();

            $table->timestamps();

            // Index pour les scopes parAnnee() et parType()
            $table->index(['annee_scolaire', 'date_debut']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendrier');
    }
};
