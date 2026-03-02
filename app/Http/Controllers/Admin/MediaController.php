<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Media::where('model_type', Activite::class)
            ->where('collection_name', 'photos')
            ->orderByDesc('created_at');

        $medias = $query->paginate($request->get('per_page', 30));

        $formatted = $medias->through(fn ($m) => [
            'id' => $m->id,
            'nom' => $m->name,
            'url' => $m->getUrl(),
            'url_thumb' => $m->getUrl('thumb'),
            'taille' => $m->size,
            'activite_id' => $m->model_id,
            'created_at' => $m->created_at,
        ]);

        return response()->json(['success' => true, 'data' => $formatted]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'activite_id' => 'required|exists:activites,id',
            'fichier' => 'required|file|mimes:jpg,jpeg,png,webp,mp4|max:51200',
            'collection' => 'sometimes|in:photos,videos',
        ]);

        $activite = Activite::findOrFail($request->activite_id);
        $collection = $request->get('collection', 'photos');

        $media = $activite->addMedia($request->file('fichier'))
            ->toMediaCollection($collection);

        return response()->json([
            'success' => true,
            'message' => 'Fichier uploadé avec succès.',
            'data' => [
                'id' => $media->id,
                'url' => $media->getUrl(),
                'url_thumb' => $collection === 'photos' ? $media->getUrl('thumb') : null,
            ],
        ], 201);
    }

    public function destroy(Media $media): JsonResponse
    {
        $media->delete();

        return response()->json(['success' => true, 'message' => 'Média supprimé.']);
    }
}