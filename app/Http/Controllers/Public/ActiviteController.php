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

            // Seule la couverture est nécessaire pour la liste (pas les conversions)
            $activites->through(function ($a) {
                try {
                    $a->photo_couverture = $a->getFirstMediaUrl('photos') ?: null;
                    $a->photos_count     = $a->media()->where('collection_name', 'photos')->count();
                } catch (\Exception $e) {
                    Log::warning("Erreur photo activité {$a->id}: " . $e->getMessage());
                    $a->photo_couverture = null;
                    $a->photos_count     = 0;
                }
                return $a;
            });

            return response()->json(['success' => true, 'data' => $activites]);

        } catch (\Exception $e) {
            Log::error('Erreur index activités: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des activités',
            ], 500);
        }
    }

    public function show(string $slug): JsonResponse
    {
        try {
            if (empty($slug)) {
                return response()->json(['success' => false, 'message' => 'Slug invalide'], 400);
            }

            // Charge la relation 'media' en une seule requête (évite le N+1)
            $activite = Activite::publiees()
                ->with('media')
                ->where('slug', $slug)
                ->first();

            if (!$activite && is_numeric($slug)) {
                $activite = Activite::publiees()
                    ->with('media')
                    ->where('id', $slug)
                    ->first();
            }

            if (!$activite) {
                return response()->json(['success' => false, 'message' => 'Activité non trouvée'], 404);
            }

            $mediaItems = $activite->getMedia('photos');

            // ── Normalise TOUTES les photos pour le frontend ──
            // ActiviteDetail.jsx lit : act.media || act.photos
            // Chaque item doit avoir : original_url, preview_url, conversions_urls
            $activite->photos = $mediaItems->map(fn ($m) => [
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

            // Photo de couverture = medium de la 1ère photo (ou original)
            $firstMedia             = $mediaItems->first();
            $activite->photo_couverture = $firstMedia
                ? ($this->safeConversionUrl($firstMedia, 'medium') ?: $firstMedia->getUrl())
                : null;

            $activite->photos_count   = $mediaItems->count();
            $activite->date_formatted = $activite->date_activite
                ? $activite->date_activite->translatedFormat('d F Y')
                : null;

            // On retire la relation brute 'media' pour éviter que le frontend
            // lise act.media (objets Spatie bruts) au lieu de act.photos (normalisé)
            $activite->unsetRelation('media');

            return response()->json(['success' => true, 'data' => $activite]);

        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Activité non trouvée'], 404);
        } catch (\Exception $e) {
            Log::error('Erreur show activité: ' . $e->getMessage(), [
                'slug'  => $slug,
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement de l\'activité',
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Retourne l'URL d'une conversion si elle existe,
    // sinon l'URL originale, sinon null.
    // ─────────────────────────────────────────────────────────────
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
