<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Activite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

class ActiviteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Activite::publiees()
                ->orderByDesc('date_activite');

            if ($request->has('section') && !empty($request->section)) {
                $query->where(function ($q) use ($request) {
                    $q->where('section', $request->section)
                      ->orWhere('section', 'toutes');
                });
            }

            $activites = $query->paginate($request->get('per_page', 9));

            // Ajouter les URLs des photos SANS utiliser les conversions
            $activites->through(function ($a) {
                try {
                    $firstMedia = $a->getFirstMedia('photos');
                    // Utiliser getUrl() SANS paramètre (pas de 'thumb')
                    $a->photo_couverture = $firstMedia 
                        ? $firstMedia->getUrl() 
                        : null;
                    
                    $a->photos_count = $a->getMedia('photos')->count();
                    
                } catch (\Exception $e) {
                    Log::warning("Erreur chargement photo pour activité {$a->id}: " . $e->getMessage());
                    $a->photo_couverture = null;
                    $a->photos_count = 0;
                }
                return $a;
            });

            return response()->json([
                'success' => true, 
                'data' => $activites
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur index activités: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des activités'
            ], 500);
        }
    }

    public function show(string $slug): JsonResponse
    {
        try {
            // Vérifier que le slug n'est pas vide
            if (empty($slug)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Slug invalide'
                ], 400);
            }

            // Rechercher l'activité publiée avec ce slug
            $activite = Activite::publiees()
                ->where('slug', $slug)
                ->first();

            // Si non trouvé par slug, essayer par ID (au cas où)
            if (!$activite && is_numeric($slug)) {
                $activite = Activite::publiees()
                    ->where('id', $slug)
                    ->first();
            }

            // Si toujours pas trouvé, retourner 404
            if (!$activite) {
                return response()->json([
                    'success' => false,
                    'message' => 'Activité non trouvée'
                ], 404);
            }

            // Charger les photos SANS utiliser les conversions
            try {
                $photos = $activite->getMedia('photos');
                
                // Mapper les photos sans conversions
                $activite->photos = $photos->map(function ($m) {
                    return [
                        'id' => $m->id,
                        'url' => $m->getUrl(), // URL originale
                        'thumb' => $m->getUrl(), // Même URL (pas de conversion)
                        'medium' => $m->getUrl(), // Même URL (pas de conversion)
                        'name' => $m->name,
                        'size' => $m->size,
                        'mime' => $m->mime_type,
                    ];
                })->values();

                // Photo de couverture (première photo)
                $activite->photo_couverture = $photos->isNotEmpty() 
                    ? $photos->first()->getUrl()
                    : null;

                $activite->photos_count = $photos->count();

            } catch (\Exception $e) {
                Log::warning("Erreur chargement médias pour activité {$activite->id}: " . $e->getMessage());
                $activite->photos = [];
                $activite->photo_couverture = null;
                $activite->photos_count = 0;
            }

            // Ajouter la date formatée
            $activite->date_formatted = $activite->date_activite 
                ? $activite->date_activite->format('d F Y') 
                : null;

            return response()->json([
                'success' => true, 
                'data' => $activite
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Activité non trouvée'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Erreur show activité: ' . $e->getMessage(), [
                'slug' => $slug,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement de l\'activité'
            ], 500);
        }
    }
}
