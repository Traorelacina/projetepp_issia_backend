<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Activite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActiviteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Activite::publiees()
            ->orderByDesc('date_activite');

        if ($request->has('section')) {
            $query->where(function ($q) use ($request) {
                $q->where('section', $request->section)
                  ->orWhere('section', 'toutes');
            });
        }

        $activites = $query->paginate($request->get('per_page', 9));

        // Ajouter les URLs des thumbnails
        $activites->through(function ($a) {
            $a->photo_couverture = $a->getFirstMediaUrl('photos', 'thumb');
            return $a;
        });

        return response()->json(['success' => true, 'data' => $activites]);
    }

    public function show(string $slug): JsonResponse
    {
        $activite = Activite::publiees()
            ->where('slug', $slug)
            ->firstOrFail();

        $activite->photos = $activite->getMedia('photos')->map(fn ($m) => [
            'id' => $m->id,
            'url' => $m->getUrl(),
            'thumb' => $m->getUrl('thumb'),
            'medium' => $m->getUrl('medium'),
        ]);

        return response()->json(['success' => true, 'data' => $activite]);
    }
}