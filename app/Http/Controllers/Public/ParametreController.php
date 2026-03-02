<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Parametre;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ParametreController extends Controller
{
    // Liste de paramètres publics autorisés (ne jamais exposer des données sensibles)
    private const CLES_PUBLIQUES = [
        'mot_directeur',
        'nom_directeur',
        'photo_directeur',
        'horaires',
        'telephone',
        'email',
        'adresse',
        'annee_scolaire_courante',
        'inscriptions_ouvertes',
        'date_rentree',
        'scolarite_montant',
    ];

    public function index(): JsonResponse
    {
        $parametres = Parametre::whereIn('cle', self::CLES_PUBLIQUES)
            ->get(['cle', 'valeur'])
            ->pluck('valeur', 'cle');

        return response()->json(['success' => true, 'data' => $parametres]);
    }

    public function show(string $cle): JsonResponse
    {
        if (!in_array($cle, self::CLES_PUBLIQUES)) {
            return response()->json(['success' => false, 'message' => 'Paramètre non disponible.'], 403);
        }

        $parametre = Parametre::where('cle', $cle)->firstOrFail();

        return response()->json(['success' => true, 'data' => $parametre]);
    }

    public function adminIndex(): JsonResponse
    {
        $parametres = Parametre::all()->pluck('valeur', 'cle');
        return response()->json(['success' => true, 'data' => $parametres]);
    }

    public function update(Request $request, string $cle): JsonResponse
    {
        $request->validate([
            'valeur' => 'required',
        ]);

        Parametre::set($cle, $request->valeur);

        return response()->json([
            'success' => true,
            'message' => 'Paramètre mis à jour.',
        ]);
    }
}