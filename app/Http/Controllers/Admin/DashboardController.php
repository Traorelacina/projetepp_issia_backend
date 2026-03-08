<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Actualite;
use App\Models\Activite;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $stats = [
            // Statistiques des actualités
            'actualites_publiees' => Actualite::where('statut', Actualite::STATUT_PUBLIE)->count(),
            'actualites_brouillons' => Actualite::where('statut', Actualite::STATUT_BROUILLON)->count(),
            
            // Statistiques des activités
            'activites_total' => Activite::count(),
            'activites_publiees' => Activite::where('publie', true)->count(),
            
            // Statistiques des messages
            'messages_non_lus' => Message::nonLus()->count(),
            'messages_total' => Message::nonArchives()->count(),
        ];

        // Derniers messages
        $derniers_messages = Message::nonArchives()
            ->orderByDesc('created_at')
            ->limit(5)
            ->get(['id', 'nom', 'email', 'sujet', 'lu', 'created_at']);

        // Dernières actualités
        $dernieres_actualites = Actualite::with('auteur:id,name')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get(['id', 'user_id', 'contenu', 'type', 'statut', 'date_publication', 'created_at']);

        // Dernières activités
        $dernieres_activites = Activite::with('section')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get(['id', 'titre', 'slug', 'section', 'photo_principale', 'created_at']);

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats,
                'derniers_messages' => $derniers_messages,
                'dernieres_actualites' => $dernieres_actualites,
                'dernieres_activites' => $dernieres_activites,
            ],
        ]);
    }
}
