<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Inscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InscriptionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'annee_scolaire' => 'required|string|max:10',
            'section' => 'required|in:creche,ps,ms,gs',
            'nom_enfant' => 'required|string|max:100',
            'prenoms_enfant' => 'required|string|max:150',
            'date_naissance' => 'required|date|before:today',
            'lieu_naissance' => 'required|string|max:100',
            'sexe' => 'required|in:M,F',
            'nationalite' => 'nullable|string|max:50',
            'nom_pere' => 'nullable|string|max:100',
            'profession_pere' => 'nullable|string|max:100',
            'telephone_pere' => 'nullable|string|max:20',
            'nom_mere' => 'nullable|string|max:100',
            'profession_mere' => 'nullable|string|max:100',
            'telephone_mere' => 'nullable|string|max:20',
            'nom_tuteur' => 'nullable|string|max:100',
            'telephone_tuteur' => 'nullable|string|max:20',
            'adresse_domicile' => 'nullable|string|max:255',
            'cantine' => 'boolean',
            'ancienne_ecole' => 'nullable|string|max:150',
            'observations' => 'nullable|string',
        ]);

        $inscription = Inscription::create([
            ...$validated,
            'statut' => Inscription::STATUT_EN_ATTENTE,
            'statut_paiement' => Inscription::PAIEMENT_NON_PAYE,
            'montant_restant' => Inscription::SCOLARITE_TOTALE,
            'montant_verse' => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Votre demande d\'inscription a été soumise avec succès. La direction du CPPE vous contactera pour confirmation.',
            'data' => ['id' => $inscription->id],
        ], 201);
    }
}