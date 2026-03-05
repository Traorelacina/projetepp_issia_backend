<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Parametre;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ParametreController extends Controller
{
    // Liste de paramètres publics autorisés (ne jamais exposer des données sensibles)
    private const CLES_PUBLIQUES = [
        'mot_directeur',
        'nom_directeur',
        'photo_directeur',
        'horaires',
        'telephone',
        'email',
        'adresse',
        'annee_scolaire_courante',
        'inscriptions_ouvertes',
        'date_rentree',
        'scolarite_montant',
    ];

    public function index(): JsonResponse
    {
        try {
            $parametres = Parametre::whereIn('cle', self::CLES_PUBLIQUES)
                ->get(['cle', 'valeur'])
                ->pluck('valeur', 'cle');

            // Ajouter l'URL complète pour la photo du directeur
            if (isset($parametres['photo_directeur']) && $parametres['photo_directeur']) {
                if (str_starts_with($parametres['photo_directeur'], 'data:image')) {
                    // C'est déjà une image base64, on garde telle quelle
                    $parametres['photo_directeur_url'] = $parametres['photo_directeur'];
                } else {
                    // C'est un chemin de fichier
                    $parametres['photo_directeur_url'] = Storage::disk('public')->url($parametres['photo_directeur']);
                }
            }

            return response()->json(['success' => true, 'data' => $parametres]);
        } catch (\Exception $e) {
            Log::error('Erreur index paramètres: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erreur serveur'], 500);
        }
    }

    public function show(string $cle): JsonResponse
    {
        try {
            if (!in_array($cle, self::CLES_PUBLIQUES)) {
                return response()->json(['success' => false, 'message' => 'Paramètre non disponible.'], 403);
            }

            $parametre = Parametre::where('cle', $cle)->firstOrFail();
            
            $data = $parametre->toArray();
            if ($cle === 'photo_directeur' && $parametre->valeur) {
                if (str_starts_with($parametre->valeur, 'data:image')) {
                    $data['url'] = $parametre->valeur;
                } else {
                    $data['url'] = Storage::disk('public')->url($parametre->valeur);
                }
            }

            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            Log::error("Erreur show paramètre {$cle}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erreur serveur'], 500);
        }
    }

    public function adminIndex(): JsonResponse
    {
        try {
            $parametres = Parametre::all()->pluck('valeur', 'cle');
            
            // Ajouter l'URL pour la photo
            if (isset($parametres['photo_directeur']) && $parametres['photo_directeur']) {
                if (str_starts_with($parametres['photo_directeur'], 'data:image')) {
                    $parametres['photo_directeur_url'] = $parametres['photo_directeur'];
                } else {
                    $parametres['photo_directeur_url'] = Storage::disk('public')->url($parametres['photo_directeur']);
                }
            }

            return response()->json(['success' => true, 'data' => $parametres]);
        } catch (\Exception $e) {
            Log::error('Erreur adminIndex paramètres: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erreur serveur'], 500);
        }
    }

    public function update(Request $request, string $cle): JsonResponse
    {
        try {
            // Pour la photo, on a une route spéciale
            if ($cle === 'photo_directeur') {
                return $this->uploadPhotoDirecteur($request);
            }

            // Pour les champs texte normaux
            $request->validate([
                'valeur' => 'required',
            ]);

            Parametre::set($cle, $request->valeur);

            return response()->json([
                'success' => true,
                'message' => 'Paramètre mis à jour.',
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error("Erreur update paramètre {$cle}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour'
            ], 500);
        }
    }

    /**
     * Méthode spéciale pour l'upload de la photo du directeur
     */
    public function uploadPhotoDirecteur(Request $request): JsonResponse
    {
        try {
            // Si c'est une image base64 (envoyée depuis le frontend)
            if ($request->has('valeur') && str_starts_with($request->valeur, 'data:image')) {
                $base64Image = $request->valeur;
                
                // Extraire le type et les données
                if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $matches)) {
                    $imageType = $matches[1];
                    $imageData = substr($base64Image, strpos($base64Image, ',') + 1);
                    $imageData = base64_decode($imageData);
                    
                    if ($imageData === false) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Données image invalides'
                        ], 400);
                    }
                    
                    // Générer un nom de fichier unique
                    $nomFichier = 'directeur_' . time() . '_' . uniqid() . '.' . $imageType;
                    $dossier = 'photos/directeur';
                    
                    // Créer le dossier s'il n'existe pas
                    if (!Storage::disk('public')->exists($dossier)) {
                        Storage::disk('public')->makeDirectory($dossier);
                    }
                    
                    // Supprimer l'ancienne photo si elle existe et c'est un fichier
                    $anciennePhoto = Parametre::where('cle', 'photo_directeur')->first();
                    if ($anciennePhoto && $anciennePhoto->valeur && !str_starts_with($anciennePhoto->valeur, 'data:image')) {
                        if (Storage::disk('public')->exists($anciennePhoto->valeur)) {
                            Storage::disk('public')->delete($anciennePhoto->valeur);
                        }
                    }
                    
                    // Sauvegarder le fichier
                    $path = $dossier . '/' . $nomFichier;
                    Storage::disk('public')->put($path, $imageData);
                    
                    // Sauvegarder en base
                    Parametre::updateOrCreate(
                        ['cle' => 'photo_directeur'],
                        ['valeur' => $path]
                    );
                    
                    return response()->json([
                        'success' => true,
                        'message' => 'Photo téléchargée avec succès',
                        'data' => [
                            'path' => $path,
                            'url' => Storage::disk('public')->url($path)
                        ]
                    ]);
                }
            }
            
            // Sinon, validation du fichier uploadé
            $request->validate([
                'photo' => 'required|image|mimes:jpeg,png,jpg|max:5120' // 5MB max
            ]);

            if (!$request->hasFile('photo')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun fichier reçu'
                ], 400);
            }

            $file = $request->file('photo');
            
            if (!$file->isValid()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Fichier invalide'
                ], 400);
            }

            // Créer le dossier s'il n'existe pas
            $dossier = 'photos/directeur';
            if (!Storage::disk('public')->exists($dossier)) {
                Storage::disk('public')->makeDirectory($dossier);
            }

            // Supprimer l'ancienne photo si elle existe et c'est un fichier
            $anciennePhoto = Parametre::where('cle', 'photo_directeur')->first();
            if ($anciennePhoto && $anciennePhoto->valeur && !str_starts_with($anciennePhoto->valeur, 'data:image')) {
                if (Storage::disk('public')->exists($anciennePhoto->valeur)) {
                    Storage::disk('public')->delete($anciennePhoto->valeur);
                }
            }

            // Générer un nom unique
            $extension = $file->getClientOriginalExtension();
            $nomFichier = 'directeur_' . time() . '_' . uniqid() . '.' . $extension;
            
            // Upload de la nouvelle photo
            $path = $file->storeAs($dossier, $nomFichier, 'public');
            
            if (!$path) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de l\'enregistrement du fichier'
                ], 500);
            }

            // Sauvegarder le chemin en base
            $parametre = Parametre::updateOrCreate(
                ['cle' => 'photo_directeur'],
                ['valeur' => $path]
            );

            return response()->json([
                'success' => true,
                'message' => 'Photo téléchargée avec succès',
                'data' => [
                    'path' => $path,
                    'url' => Storage::disk('public')->url($path)
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erreur upload photo directeur: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'upload: ' . $e->getMessage()
            ], 500);
        }
    }
}
