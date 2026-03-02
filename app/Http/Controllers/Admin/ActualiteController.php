<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Actualite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActualiteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Actualite::with('auteur:id,name')
            ->orderByDesc('created_at');

        if ($request->has('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('search')) {
            $query->where('titre', 'like', "%{$request->search}%");
        }

        $actualites = $query->paginate($request->get('per_page', 20));

        return response()->json(['success' => true, 'data' => $actualites]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create-actualite');

        $validated = $request->validate([
            'titre' => 'required|string|max:255',
            'contenu' => 'required|string',
            'type' => 'required|in:flash,convocation,evenement,inscription',
            'statut' => 'required|in:brouillon,publie,planifie',
            'date_publication' => 'nullable|date',
            'date_expiration' => 'nullable|date|after:date_publication',
        ]);

        $actualite = Actualite::create([
            ...$validated,
            'user_id' => $request->user()->id,
            'date_publication' => $validated['statut'] === 'publie'
                ? ($validated['date_publication'] ?? now())
                : $validated['date_publication'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Actualité créée avec succès.',
            'data' => $actualite->load('auteur:id,name'),
        ], 201);
    }

    public function show(Actualite $actualite): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $actualite->load('auteur:id,name'),
        ]);
    }

    public function update(Request $request, Actualite $actualite): JsonResponse
    {
        $this->authorize('edit-actualite');

        $validated = $request->validate([
            'titre' => 'sometimes|string|max:255',
            'contenu' => 'sometimes|string',
            'type' => 'sometimes|in:flash,convocation,evenement,inscription',
            'statut' => 'sometimes|in:brouillon,publie,planifie,archive',
            'date_publication' => 'nullable|date',
            'date_expiration' => 'nullable|date',
        ]);

        $actualite->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Actualité mise à jour.',
            'data' => $actualite->load('auteur:id,name'),
        ]);
    }

    public function destroy(Actualite $actualite): JsonResponse
    {
        $this->authorize('delete-actualite');
        $actualite->delete();

        return response()->json([
            'success' => true,
            'message' => 'Actualité supprimée.',
        ]);
    }

    public function updateStatut(Request $request, Actualite $actualite): JsonResponse
    {
        $this->authorize('publish-actualite');

        $request->validate([
            'statut' => 'required|in:brouillon,publie,planifie,archive',
        ]);

        $actualite->update([
            'statut' => $request->statut,
            'date_publication' => $request->statut === 'publie' ? now() : $actualite->date_publication,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Statut mis à jour.',
            'data' => $actualite,
        ]);
    }
}