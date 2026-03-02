<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Table : inscriptions
 *
 * Champs déduits de :
 *  - Public\InscriptionController  : validate() store — tous les champs de la fiche
 *  - Admin\InscriptionController   : updateStatut(), exportCsv(), scopes
 *  - DashboardController           : parAnnee(), parSection(), count()
 *
 * Constantes du modèle Inscription :
 *   STATUT_EN_ATTENTE  = 'en_attente'
 *   PAIEMENT_NON_PAYE  = 'non_paye'
 *   SCOLARITE_TOTALE   = 50000  (FCFA)
 *
 * Scopes attendus :
 *   parAnnee($annee), parSection($section), parStatut($statut)
 *
 * Particularités :
 *  - section : 'creche','ps','ms','gs'  (pas 'toutes' — on inscrit dans une section précise)
 *  - montant_verse  + montant_restant  gérés dans updateStatut()
 *  - cantine   : boolean (enfant inscrit à la cantine)
 *  - ancienne_ecole : école précédente si réinscription
 *  - nationalite : champ présent dans validate() public
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inscriptions', function (Blueprint $table) {
            $table->id();

            // ── Contexte scolaire ─────────────────────────────────────
            $table->string('annee_scolaire', 10)->default('2025-2026');

            $table->enum('section', [
                'creche',
                'ps',
                'ms',
                'gs',
            ]);

            // ── Informations enfant ───────────────────────────────────
            $table->string('nom_enfant', 100);
            $table->string('prenoms_enfant', 150);
            $table->date('date_naissance');
            $table->string('lieu_naissance', 100);
            $table->enum('sexe', ['M', 'F']);
            $table->string('nationalite', 50)->nullable()->default('Ivoirienne');

            // ── Père ──────────────────────────────────────────────────
            $table->string('nom_pere', 100)->nullable();
            $table->string('profession_pere', 100)->nullable();
            $table->string('telephone_pere', 20)->nullable();

            // ── Mère ──────────────────────────────────────────────────
            $table->string('nom_mere', 100)->nullable();
            $table->string('profession_mere', 100)->nullable();
            $table->string('telephone_mere', 20)->nullable();

            // ── Tuteur légal ──────────────────────────────────────────
            $table->string('nom_tuteur', 100)->nullable();
            $table->string('telephone_tuteur', 20)->nullable();

            // ── Domicile ──────────────────────────────────────────────
            $table->string('adresse_domicile', 255)->nullable();

            // ── Options ───────────────────────────────────────────────
            $table->boolean('cantine')->default(false);
            $table->string('ancienne_ecole', 150)->nullable();
            $table->text('observations')->nullable();

            // ── Workflow statut ───────────────────────────────────────
            $table->enum('statut', [
                'en_attente',
                'valide',
                'refuse',
            ])->default('en_attente');

            // ── Paiement ──────────────────────────────────────────────
            $table->enum('statut_paiement', [
                'non_paye',
                'partiel',
                'complet',
            ])->default('non_paye');

            // montant_verse : mis à jour dans updateStatut()
            $table->decimal('montant_verse', 10, 0)->default(0);

            // montant_restant = SCOLARITE_TOTALE(50000) - montant_verse
            $table->decimal('montant_restant', 10, 0)->default(50000);

            $table->timestamps();
            $table->softDeletes();

            // Index pour les filtres admin et scopes
            $table->index(['annee_scolaire', 'section']);
            $table->index(['annee_scolaire', 'statut']);
            $table->index('statut_paiement');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inscriptions');
    }
};
