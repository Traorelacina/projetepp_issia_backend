<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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

            return response()->json([
                'success' => true, 
                'data' => $query->paginate(20)
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur index activites: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des activités'
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'titre' => 'required|string|max:255',
                'description' => 'required|string',
                'date_activite' => 'required|date',
                'publie' => 'sometimes|boolean',
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
                    if ($photo->isValid()) {
                        $activite->addMedia($photo)->toMediaCollection('photos');
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Activité créée avec succès.',
                'data' => $activite->load('auteur:id,name'),
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Erreur création activité: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(Activite $activite): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $activite->load('auteur:id,name')->load('media'),
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur affichage activité: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement de l\'activité'
            ], 500);
        }
    }

    public function update(Request $request, Activite $activite): JsonResponse
    {
        try {
            // Log des données reçues pour débogage
            \Log::info('Données reçues pour mise à jour:', [
                'all' => $request->all(),
                'files' => $request->hasFile('photos') ? count($request->file('photos')) : 0,
                'method' => $request->method(),
                'content_type' => $request->header('Content-Type')
            ]);

            // Gérer à la fois PUT, POST et POST avec _method
            $rules = [
                'titre' => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
                'date_activite' => 'sometimes|date',
                'publie' => 'sometimes|boolean',
            ];

            // Validation personnalisée pour gérer les différents formats
            $validated = [];
            
            if ($request->has('titre')) {
                $validated['titre'] = $request->input('titre');
            }
            
            if ($request->has('description')) {
                $validated['description'] = $request->input('description');
            }
            
            if ($request->has('date_activite')) {
                $validated['date_activite'] = $request->input('date_activite');
            }
            
            if ($request->has('publie')) {
                // Gérer à la fois string '1'/'0' et boolean true/false
                $publie = $request->input('publie');
                if (is_string($publie)) {
                    $validated['publie'] = $publie === '1' || $publie === 'true';
                } else {
                    $validated['publie'] = (bool) $publie;
                }
            }

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

            if (!empty($dataToUpdate)) {
                $activite->update($dataToUpdate);
                \Log::info('Activité mise à jour avec:', $dataToUpdate);
            }

            // Upload des nouvelles photos
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    if ($photo->isValid()) {
                        $activite->addMedia($photo)->toMediaCollection('photos');
                        \Log::info('Photo ajoutée: ' . $photo->getClientOriginalName());
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Activité mise à jour avec succès.',
                'data' => $activite->load('auteur:id,name')->load('media'),
            ]);

        } catch (ValidationException $e) {
            \Log::warning('Erreur validation update: ' . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Activité non trouvée'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Erreur mise à jour activité: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Activite $activite): JsonResponse
    {
        try {
            $activite->clearMediaCollection('photos');
            $activite->clearMediaCollection('videos');
            $activite->delete();

            return response()->json([
                'success' => true, 
                'message' => 'Activité supprimée avec succès.'
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur suppression activité: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression'
            ], 500);
        }
    }
}
