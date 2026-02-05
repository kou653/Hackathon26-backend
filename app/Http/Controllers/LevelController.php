<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Niveau;
use App\Models\Classe;

class LevelController extends Controller
{
    // Récupérer les niveaux et les classes selon le type d'école
    public function getLevelsAndClasses(Request $request)
    {
        try {
            $esatic = $request->input('esatic', 1); // Par défaut ESATIC
            
            // Récupérer les niveaux avec leurs classes
            $niveaux = Niveau::with('classes')
                ->where('esatic', $esatic) // Filtrer selon esatic ou non
                ->get()
                ->map(function ($niveau) {
                    return [
                        'id' => $niveau->id,
                        'libelle' => $niveau->libelle,
                        'classes' => $niveau->classes->map(function ($classe) {
                            return [
                                'id' => $classe->id,
                                'libelle' => $classe->libelle,
                            ];
                        })
                    ];
                });

            return response()->json([
                'success' => true,
                'niveaux' => $niveaux
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des données',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Récupérer les classes d'un niveau spécifique
    public function getClassesByLevel($levelId)
    {
        try {
            $classes = Classe::where('niveau_id', $levelId)
                ->get()
                ->map(function ($classe) {
                    return [
                        'id' => $classe->id,
                        'libelle' => $classe->libelle,
                    ];
                });

            return response()->json([
                'success' => true,
                'classes' => $classes
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des classes',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}