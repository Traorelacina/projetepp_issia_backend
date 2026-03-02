<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'annee_scolaire',
        'section',
        // Infos enfant
        'nom_enfant',
        'prenoms_enfant',
        'date_naissance',
        'lieu_naissance',
        'sexe',
        'nationalite',
        // Infos parent/tuteur
        'nom_pere',
        'profession_pere',
        'telephone_pere',
        'nom_mere',
        'profession_mere',
        'telephone_mere',
        'nom_tuteur',
        'telephone_tuteur',
        'adresse_domicile',
        // Infos paiement
        'statut_paiement',
        'montant_verse',
        'montant_restant',
        'date_premier_versement',
        'date_deuxieme_versement',
        'date_troisieme_versement',
        // Infos complémentaires
        'cantine',
        'ancienne_ecole',
        'observations',
        'statut',
    ];

    protected function casts(): array
    {
        return [
            'date_naissance' => 'date',
            'date_premier_versement' => 'date',
            'date_deuxieme_versement' => 'date',
            'date_troisieme_versement' => 'date',
            'cantine' => 'boolean',
            'montant_verse' => 'decimal:0',
            'montant_restant' => 'decimal:0',
        ];
    }

    const SECTIONS = ['creche', 'ps', 'ms', 'gs'];
    const SCOLARITE_TOTALE = 50000; // FCFA

    const STATUT_EN_ATTENTE = 'en_attente';
    const STATUT_VALIDE = 'valide';
    const STATUT_REFUSE = 'refuse';

    const PAIEMENT_NON_PAYE = 'non_paye';
    const PAIEMENT_PARTIEL = 'partiel';
    const PAIEMENT_COMPLET = 'complet';

    public function getNomCompletEnfantAttribute(): string
    {
        return "{$this->prenoms_enfant} {$this->nom_enfant}";
    }

    public function getAgeAttribute(): string
    {
        return $this->date_naissance->diffForHumans(null, true);
    }

    public function scopeParSection($query, string $section)
    {
        return $query->where('section', $section);
    }

    public function scopeParAnnee($query, string $annee)
    {
        return $query->where('annee_scolaire', $annee);
    }

    public function scopeParStatut($query, string $statut)
    {
        return $query->where('statut', $statut);
    }
}