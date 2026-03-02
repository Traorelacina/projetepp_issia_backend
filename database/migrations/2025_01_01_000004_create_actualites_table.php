<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Table : actualites
 *
 * Champs déduits de :
 *  - Admin\ActualiteController  : validate() store/update
 *  - Public\ActualiteController : publiees(), deType()
 *  - DashboardController        : STATUT_PUBLIE, STATUT_BROUILLON constants
 *
 * Constantes du modèle Actualite :
 *   STATUT_PUBLIE   = 'publie'
 *   STATUT_BROUILLON= 'brouillon'
 *
 * Scopes attendus : publiees(), deType(), parAnnee()
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('actualites', function (Blueprint $table) {
            $table->id();

            // Contenu
            $table->string('titre');
            $table->longText('contenu');

            // Catégorisation
            $table->enum('type', [
                'flash',
                'convocation',
                'evenement',
                'inscription',
            ])->default('flash');

            // Workflow de publication
            $table->enum('statut', [
                'brouillon',
                'publie',
                'planifie',
                'archive',
            ])->default('brouillon');

            // Dates de publication
            // date_publication : nulle si brouillon, sinon now() ou date planifiée
            $table->timestamp('date_publication')->nullable();

            // date_expiration : validate() -> 'after:date_publication'
            $table->timestamp('date_expiration')->nullable();

            // Auteur (admin connecté)
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Index pour les requêtes fréquentes
            $table->index(['statut', 'date_publication']);
            $table->index('type');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('actualites');
    }
};
