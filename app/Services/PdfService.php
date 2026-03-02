<?php

namespace App\Services;

use App\Models\Inscription;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfService
{
    public function ficheEnfant(Inscription $inscription)
    {
        // ── Logo en base64 pour que DomPDF puisse l'intégrer sans requête HTTP ──
        $logoPath  = public_path('images/logo.jpeg');
        $logoBase64 = null;

        if (file_exists($logoPath)) {
            $mimeType   = mime_content_type($logoPath); // 'image/jpeg' ou 'image/png'
            $logoBase64 = 'data:' . $mimeType . ';base64,' . base64_encode(file_get_contents($logoPath));
        }

        $data = [
            'insc'            => $inscription,          // ← variable attendue dans le Blade
            'logo_base64'     => $logoBase64,           // string data:image/jpeg;base64,... ou null
            'date_impression' => now()->locale('fr')->isoFormat('D MMMM YYYY'),
            'annee_scolaire'  => config('cppe.annee_scolaire', '2025-2026'),
        ];

        return Pdf::loadView('pdf.fiche-enfant', $data)
                  ->setPaper('a4', 'portrait');
    }

    public function convocationParents(array $data)
    {
        return Pdf::loadView('pdf.convocation', $data)
                  ->setPaper('a4', 'portrait');
    }
}