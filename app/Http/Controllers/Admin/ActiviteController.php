<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActiviteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Activite::with(['auteur:id,name'])
                        ->withCount('media')
                        ->orderByDesc('date_activite');

        // Filtre par statut de publication uniquement
        if ($request->has('publie')) {
            $query->where('publie', $request->boolean('publie'));
        }

        return response()->json(['success' => true, 'data' => $query->paginate(20)]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'required|string',
            'date_activite' => 'required|date',
            'publie' => 'boolean',
        ]);

        $activite = Activite::create([
            'titre' => $validated['titre'],
            'description' => $validated['description'],
            'date_activite' => $validated['date_activite'],
            'publie' => $validated['publie'] ?? false,
            'user_id' => $request->user()->id,
            'slug' => Activite::genererSlug($validated['titre']),
        ]);

        // Upload des médias si fournis
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $activite->addMedia($photo)->toMediaCollection('photos');
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Activité créée avec succès.',
            'data' => $activite->load('auteur:id,name'),
        ], 201);
    }

    public function show(Activite $activite): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $activite->load('auteur:id,name')->append('media'),
        ]);
    }

    public function update(Request $request, Activite $activite): JsonResponse
    {
        $validated = $request->validate([
            'titre' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'date_activite' => 'sometimes|date',
            'publie' => 'sometimes|boolean',
        ]);

        $dataToUpdate = [];

        if (isset($validated['titre'])) {
            $dataToUpdate['titre'] = $validated['titre'];
            $dataToUpdate['slug'] = Activite::genererSlug($validated['titre']);
        }

        if (isset($validated['description'])) {
            $dataToUpdate['description'] = $validated['description'];
        }

        if (isset($validated['date_activite'])) {
            $dataToUpdate['date_activite'] = $validated['date_activite'];
        }

        if (isset($validated['publie'])) {
            $dataToUpdate['publie'] = $validated['publie'];
        }

        $activite->update($dataToUpdate);

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $activite->addMedia($photo)->toMediaCollection('photos');
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Activité mise à jour.',
            'data' => $activite->load('auteur:id,name'),
        ]);
    }

    public function destroy(Activite $activite): JsonResponse
    {
        $activite->clearMediaCollection('photos');
        $activite->clearMediaCollection('videos');
        $activite->delete();

        return response()->json(['success' => true, 'message' => 'Activité supprimée.']);
    }
}
