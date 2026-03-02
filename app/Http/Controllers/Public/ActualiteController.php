<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Actualite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActualiteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Actualite::publiees()
            ->with('auteur:id,name')
            ->orderByDesc('date_publication');

        if ($request->has('type')) {
            $query->deType($request->type);
        }

        $actualites = $query->paginate($request->get('per_page', 10));

        return response()->json(['success' => true, 'data' => $actualites]);
    }

    public function show(int $id): JsonResponse
    {
        $actualite = Actualite::publiees()->findOrFail($id);
        return response()->json(['success' => true, 'data' => $actualite]);
    }
}