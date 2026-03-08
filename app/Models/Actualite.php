<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Actualite extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'contenu',
        'type',
        'statut',
        'date_publication',
        'date_expiration',
    ];

    protected function casts(): array
    {
        return [
            'date_publication' => 'datetime',
            'date_expiration' => 'datetime',
        ];
    }

    // Types d'actualités
    const TYPE_FLASH = 'flash';
    const TYPE_CONVOCATION = 'convocation';
    const TYPE_EVENEMENT = 'evenement';
    const TYPE_INSCRIPTION = 'inscription';

    // Statuts
    const STATUT_BROUILLON = 'brouillon';
    const STATUT_PUBLIE = 'publie';
    const STATUT_PLANIFIE = 'planifie';
    const STATUT_ARCHIVE = 'archive';

    public function auteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Scope : uniquement les actualités publiées
    public function scopePubliees($query)
    {
        return $query->where('statut', self::STATUT_PUBLIE)
                     ->where(function ($q) {
                         $q->whereNull('date_expiration')
                           ->orWhere('date_expiration', '>', now());
                     });
    }

    // Scope : par type
    public function scopeDeType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
