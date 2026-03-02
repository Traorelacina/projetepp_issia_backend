<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Actualite;
use App\Models\Activite;
use App\Models\Inscription;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $anneeEnCours = config('cppe.annee_scolaire', '2025-2026');

        $stats = [
            'inscriptions_total' => Inscription::parAnnee($anneeEnCours)->count(),
            'inscriptions_ce_mois' => Inscription::parAnnee($anneeEnCours)
                ->whereMonth('created_at', now()->month)
                ->count(),
            'inscriptions_par_section' => Inscription::parAnnee($anneeEnCours)
                ->selectRaw('section, count(*) as total')
                ->groupBy('section')
                ->pluck('total', 'section'),
            'actualites_publiees' => Actualite::where('statut', Actualite::STATUT_PUBLIE)->count(),
            'actualites_brouillons' => Actualite::where('statut', Actualite::STATUT_BROUILLON)->count(),
            'activites_publiees' => Activite::where('publie', true)->count(),
            'photos_galerie' => \Spatie\MediaLibrary\MediaCollections\Models\Media::where('collection_name', 'photos')->count(),
            'messages_non_lus' => Message::nonLus()->count(),
            'messages_total' => Message::nonArchives()->count(),
        ];

        // Dernières inscriptions
        $dernieres_inscriptions = Inscription::parAnnee($anneeEnCours)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get(['id', 'nom_enfant', 'prenoms_enfant', 'section', 'statut', 'created_at']);

        // Derniers messages
        $derniers_messages = Message::nonArchives()
            ->orderByDesc('created_at')
            ->limit(5)
            ->get(['id', 'nom', 'email', 'sujet', 'lu', 'created_at']);

        // Dernières actualités
        $dernieres_actualites = Actualite::with('auteur:id,name')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get(['id', 'user_id', 'titre', 'type', 'statut', 'date_publication', 'created_at']);

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats,
                'dernieres_inscriptions' => $dernieres_inscriptions,
                'derniers_messages' => $derniers_messages,
                'dernieres_actualites' => $dernieres_actualites,
            ],
        ]);
    }
}