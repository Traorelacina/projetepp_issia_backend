<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Calendrier extends Model
{
    use HasFactory;

    protected $table = 'calendrier';

    protected $fillable = [
        'label',
        'description',
        'date_debut',
        'date_fin',
        'type',
        'annee_scolaire',
        'couleur',
    ];

    protected function casts(): array
    {
        return [
            'date_debut' => 'date',
            'date_fin' => 'date',
        ];
    }

    const TYPE_VACANCES = 'vacances';
    const TYPE_FERIE = 'ferie';
    const TYPE_RENTREE = 'rentree';
    const TYPE_EVENEMENT = 'evenement';
    const TYPE_EXAMEN = 'examen';

    public function scopeParAnnee($query, string $annee)
    {
        return $query->where('annee_scolaire', $annee);
    }

    public function scopeParType($query, string $type)
    {
        return $query->where('type', $type);
    }
}