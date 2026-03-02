<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Calendrier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CalendrierController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Calendrier::orderBy('date_debut');

        $annee = $request->get('annee', config('cppe.annee_scolaire', '2025-2026'));
        $query->parAnnee($annee);

        if ($request->has('type')) {
            $query->parType($request->type);
        }

        return response()->json(['success' => true, 'data' => $query->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'type' => 'required|in:vacances,ferie,rentree,evenement,examen',
            'annee_scolaire' => 'required|string|max:10',
            'couleur' => 'nullable|string|max:7',
        ]);

        $evenement = Calendrier::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Événement ajouté au calendrier.',
            'data' => $evenement,
        ], 201);
    }

    public function show(Calendrier $calendrier): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $calendrier]);
    }

    public function update(Request $request, Calendrier $calendrier): JsonResponse
    {
        $validated = $request->validate([
            'label' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'date_debut' => 'sometimes|date',
            'date_fin' => 'sometimes|date',
            'type' => 'sometimes|in:vacances,ferie,rentree,evenement,examen',
            'couleur' => 'nullable|string|max:7',
        ]);

        $calendrier->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Événement mis à jour.',
            'data' => $calendrier,
        ]);
    }

    public function destroy(Calendrier $calendrier): JsonResponse
    {
        $calendrier->delete();
        return response()->json(['success' => true, 'message' => 'Événement supprimé.']);
    }
}