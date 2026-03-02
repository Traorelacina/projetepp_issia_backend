<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Table : activites
 *
 * Champs déduits de :
 *  - Admin\ActiviteController  : validate() store/update, withCount('media')
 *  - Public\ActiviteController : publiees(), section filter, photo_couverture
 *  - DashboardController       : Activite::where('publie', true)->count()
 *
 * Notes importantes :
 *  - section : valeurs 'creche','ps','ms','gs','toutes'
 *    (ps/ms/gs = petite/moyenne/grande section — abréviations utilisées dans validate())
 *  - Les photos/vidéos sont stockées dans la table `media` via Spatie Media Library
 *  - Activite::genererSlug() génère le slug depuis le titre
 *  - publie est un boolean (pas un enum statut)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activites', function (Blueprint $table) {
            $table->id();

            // Contenu principal
            $table->string('titre');
            $table->string('slug')->unique();
            $table->text('description');

            // Section pédagogique
            // Valeurs : creche | ps (petite section) | ms (moyenne section) | gs (grande section) | toutes
            $table->enum('section', [
                'creche',
                'ps',
                'ms',
                'gs',
                'toutes',
            ])->default('toutes');

            // Date de l'activité (orderByDesc('date_activite') dans index())
            $table->date('date_activite');

            // Publication (boolean, pas enum)
            $table->boolean('publie')->default(false);

            // Auteur
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index('section');
            $table->index(['publie', 'date_activite']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activites');
    }
};
