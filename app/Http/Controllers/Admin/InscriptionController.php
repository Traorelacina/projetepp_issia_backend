<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inscription;
use App\Services\PdfService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InscriptionController extends Controller
{
    public function __construct(private PdfService $pdfService) {}

    public function index(Request $request): JsonResponse
    {
        $query = Inscription::orderByDesc('created_at');

        if ($request->has('annee')) {
            $query->parAnnee($request->annee);
        } else {
            $query->parAnnee(config('cppe.annee_scolaire', '2025-2026'));
        }

        if ($request->has('section')) {
            $query->parSection($request->section);
        }

        if ($request->has('statut')) {
            $query->parStatut($request->statut);
        }

        if ($request->has('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nom_enfant', 'like', "%{$s}%")
                  ->orWhere('prenoms_enfant', 'like', "%{$s}%")
                  ->orWhere('nom_pere', 'like', "%{$s}%")
                  ->orWhere('nom_mere', 'like', "%{$s}%")
                  ->orWhere('telephone_pere', 'like', "%{$s}%");
            });
        }

        $inscriptions = $query->paginate($request->get('per_page', 20));

        return response()->json(['success' => true, 'data' => $inscriptions]);
    }

    public function show(Inscription $inscription): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $inscription]);
    }

    public function updateStatut(Request $request, Inscription $inscription): JsonResponse
    {
        $request->validate([
            'statut' => 'required|in:en_attente,valide,refuse',
            'statut_paiement' => 'sometimes|in:non_paye,partiel,complet',
            'montant_verse' => 'sometimes|numeric|min:0',
        ]);

        $inscription->update($request->only(['statut', 'statut_paiement', 'montant_verse']));

        if (isset($request->montant_verse)) {
            $restant = Inscription::SCOLARITE_TOTALE - $request->montant_verse;
            $inscription->update(['montant_restant' => max(0, $restant)]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Inscription mise à jour.',
            'data' => $inscription,
        ]);
    }

    public function exportPdf(Inscription $inscription): Response
    {
        $pdf = $this->pdfService->ficheEnfant($inscription);

        return $pdf->download("fiche-{$inscription->nom_enfant}-{$inscription->prenoms_enfant}.pdf");
    }

    public function exportExcel(Request $request)
    {
        $annee = $request->get('annee', config('cppe.annee_scolaire', '2025-2026'));
        $inscriptions = Inscription::parAnnee($annee)->orderBy('section')->get();

        // Export CSV simple (peut être amélioré avec Laravel Excel)
        $csv = "Section,Nom,Prénoms,Date Naissance,Sexe,Parent,Téléphone,Statut,Paiement\n";
        foreach ($inscriptions as $i) {
            $csv .= "{$i->section},{$i->nom_enfant},{$i->prenoms_enfant},{$i->date_naissance},{$i->sexe},{$i->nom_pere} / {$i->nom_mere},{$i->telephone_pere},{$i->statut},{$i->statut_paiement}\n";
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"inscriptions-{$annee}.csv\"");
    }
}