<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Table : parametres
 *
 * Champs déduits de :
 *  - Public\ParametreController   : CLES_PUBLIQUES[], get('cle','valeur'), Parametre::set()
 *  - Admin\ParametreController    : Parametre::all()->pluck('valeur','cle')
 *
 * CLES_PUBLIQUES définies dans le controller :
 *   mot_directeur, nom_directeur, photo_directeur,
 *   horaires, telephone, email, adresse,
 *   annee_scolaire_courante, inscriptions_ouvertes,
 *   date_rentree, scolarite_montant
 *
 * Particularités :
 *  - Parametre::set($cle, $valeur) = méthode statique du modèle
 *  - Pas de softDeletes (paramètres permanents)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parametres', function (Blueprint $table) {
            $table->id();

            // Clé unique
            $table->string('cle')->unique();

            // Valeur (text pour supporter longues valeurs : mot_directeur)
            $table->text('valeur')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parametres');
    }
};
