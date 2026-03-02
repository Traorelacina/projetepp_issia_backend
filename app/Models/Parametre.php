<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Parametre extends Model
{
    protected $table = 'parametres';

    protected $fillable = [
        'cle',
        'valeur',
        'type',
        'description',
    ];

    // Clés système
    const CLE_MOT_DIRECTEUR = 'mot_directeur';
    const CLE_NOM_DIRECTEUR = 'nom_directeur';
    const CLE_PHOTO_DIRECTEUR = 'photo_directeur';
    const CLE_HORAIRES = 'horaires';
    const CLE_TELEPHONE = 'telephone';
    const CLE_EMAIL = 'email';
    const CLE_ADRESSE = 'adresse';
    const CLE_ANNEE_SCOLAIRE = 'annee_scolaire_courante';
    const CLE_INSCRIPTIONS_OUVERTES = 'inscriptions_ouvertes';
    const CLE_DATE_RENTREE = 'date_rentree';
    const CLE_SCOLARITE_MONTANT = 'scolarite_montant';

    public static function get(string $cle, $defaut = null)
    {
        $param = static::where('cle', $cle)->first();
        return $param ? $param->valeur : $defaut;
    }

    public static function set(string $cle, $valeur): void
    {
        static::updateOrCreate(['cle' => $cle], ['valeur' => $valeur]);
    }
}