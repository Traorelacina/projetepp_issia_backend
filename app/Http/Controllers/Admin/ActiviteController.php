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
        $query = Activite::with(['auteur:id,name'])->withCount('media')->orderByDesc('date_activite');

        if ($request->has('section')) {
            $query->where('section', $request->section);
        }
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
            'section' => 'required|in:creche,ps,ms,gs,toutes',
            'date_activite' => 'required|date',
            'publie' => 'boolean',
        ]);

        $activite = Activite::create([
            ...$validated,
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
            'section' => 'sometimes|in:creche,ps,ms,gs,toutes',
            'date_activite' => 'sometimes|date',
            'publie' => 'sometimes|boolean',
        ]);

        $activite->update($validated);

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