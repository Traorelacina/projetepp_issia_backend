<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

class ActiviteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Activite::with(['auteur:id,name'])
                ->withCount('media')
                ->orderByDesc('date_activite');

            if ($request->has('publie')) {
                $query->where('publie', $request->boolean('publie'));
            }

            return response()->json(['success' => true, 'data' => $query->paginate(20)]);

        } catch (\Exception $e) {
            Log::error('Erreur index activites: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des activités',
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'titre'         => 'required|string|max:255',
                'description'   => 'required|string',
                'date_activite' => 'required|date',
                'publie'        => 'sometimes|boolean',
            ]);

            $activite = Activite::create([
                'titre'         => $validated['titre'],
                'description'   => $validated['description'],
                'date_activite' => $validated['date_activite'],
                'publie'        => $validated['publie'] ?? false,
                'user_id'       => $request->user()->id,
                'slug'          => Activite::genererSlug($validated['titre']),
            ]);

            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    if ($photo->isValid()) {
                        $activite->addMedia($photo)->toMediaCollection('photos');
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Activité créée avec succès.',
                'data'    => $this->formatActivite($activite),
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erreur création activité: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show(Activite $activite): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data'    => $this->formatActivite($activite),
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur affichage activité: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement de l\'activité',
            ], 500);
        }
    }

    public function update(Request $request, Activite $activite): JsonResponse
    {
        try {
            Log::info('Données reçues pour mise à jour:', [
                'all'          => $request->except(['photos']),
                'photos_count' => $request->hasFile('photos') ? count($request->file('photos')) : 0,
                'method'       => $request->method(),
            ]);

            $dataToUpdate = [];

            if ($request->has('titre')) {
                $dataToUpdate['titre'] = $request->input('titre');
                $dataToUpdate['slug']  = Activite::genererSlug($request->input('titre'));
            }

            if ($request->has('description')) {
                $dataToUpdate['description'] = $request->input('description');
            }

            if ($request->has('date_activite')) {
                $dataToUpdate['date_activite'] = $request->input('date_activite');
            }

            if ($request->has('publie')) {
                $publie = $request->input('publie');
                $dataToUpdate['publie'] = is_string($publie)
                    ? in_array($publie, ['1', 'true'])
                    : (bool) $publie;
            }

            if (!empty($dataToUpdate)) {
                $activite->update($dataToUpdate);
                Log::info('Activité mise à jour avec:', $dataToUpdate);
            }

            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    if ($photo->isValid()) {
                        $activite->addMedia($photo)->toMediaCollection('photos');
                        Log::info('Photo ajoutée: ' . $photo->getClientOriginalName());
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Activité mise à jour avec succès.',
                'data'    => $this->formatActivite($activite),
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors'  => $e->errors(),
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Activité non trouvée'], 404);
        } catch (\Exception $e) {
            Log::error('Erreur mise à jour activité: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Activite $activite): JsonResponse
    {
        try {
            $activite->clearMediaCollection('photos');
            $activite->clearMediaCollection('videos');
            $activite->delete();

            return response()->json(['success' => true, 'message' => 'Activité supprimée avec succès.']);

        } catch (\Exception $e) {
            Log::error('Erreur suppression activité: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erreur lors de la suppression'], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Charge l'auteur + TOUTES les photos normalisées.
    // Utilisé par store / show / update pour une réponse cohérente.
    // ─────────────────────────────────────────────────────────────
    private function formatActivite(Activite $activite): array
    {
        // Recharge depuis la BDD pour avoir les médias fraîchement uploadés
        $activite->load(['auteur:id,name', 'media']);

        $mediaItems = $activite->getMedia('photos');

        // Normalise chaque photo avec les champs attendus par ActiviteDetail.jsx
        $photos = $mediaItems->map(fn ($m) => [
            'id'               => $m->id,
            'original_url'     => $m->getUrl(),
            'preview_url'      => $m->getUrl(),
            'conversions_urls' => [
                'thumb'  => $this->safeConversionUrl($m, 'thumb'),
                'medium' => $this->safeConversionUrl($m, 'medium'),
            ],
            'name'             => $m->file_name,
            'mime_type'        => $m->mime_type,
            'size'             => $m->size,
        ])->values()->toArray();

        $firstMedia = $mediaItems->first();

        return array_merge($activite->toArray(), [
            'photos'          => $photos,
            'photos_count'    => $mediaItems->count(),
            'photo_couverture' => $firstMedia
                ? ($this->safeConversionUrl($firstMedia, 'medium') ?: $firstMedia->getUrl())
                : null,
            // On retire 'media' (objets Spatie bruts) pour éviter la confusion
            // côté frontend entre act.media et act.photos
            'media'           => null,
        ]);
    }

    private function safeConversionUrl($media, string $conversion): ?string
    {
        if (!$media) return null;
        try {
            return $media->getUrl($conversion) ?: $media->getUrl();
        } catch (\Exception $e) {
            return $media->getUrl();
        }
    }
}
