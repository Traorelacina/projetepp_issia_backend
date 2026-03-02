<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Calendrier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CalendrierController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $annee = $request->get('annee', config('cppe.annee_scolaire', '2025-2026'));

        $evenements = Calendrier::parAnnee($annee)
            ->orderBy('date_debut')
            ->get();

        return response()->json(['success' => true, 'data' => $evenements]);
    }
}