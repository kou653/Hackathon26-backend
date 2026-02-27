<?php

namespace App\Http\Controllers;

use App\Models\Collation;
use App\Models\Commande;
use App\Models\Repa;
use App\Models\Salle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;

class ParticipantController extends Controller
{
    public function renderprestauration()
    {
        $user = Auth::user();
        $qrcodeValue = Crypt::encryptString($user->etudiant->matricule);
        $commandes = Commande::with(['repa', 'collation'])
            ->where('etudiant_id', $user->etudiant->id)
            ->orderBy('created_at', 'DESC')
            ->get();

        $data = [
            'qrcodeValue' => $qrcodeValue,
            'repas' => Repa::orderBy('created_at', 'DESC')->get(),
            'collations' => Collation::orderBy('created_at', 'DESC')->get(),
            'salles' => Salle::orderBy('libelle')->get(),
            'commandes' => $commandes,
            'commandes_count' => $commandes->count(),
            // Toujours false pour que le front garde le formulaire actif (commande multiple autorisee)
            'hasOrdered' => false,
        ];

        $response = [
            'status' => true,
            'data' => $data,
        ];

        return response()->json($response);
    }

    /*
    {
        'nom' => nom et prenom du participant,
        'equipe' => nom de l'equipe,
        'salle' => nom ou id de la salle,
        'repasId' => id du repas (optionnel),
        'collationId' => id de la collation (optionnel)
    }
    */
    public function makecommande(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->etudiant) {
            return response()->json([
                'status' => false,
                'message' => 'Utilisateur non autorise.',
            ], 401);
        }

        if (!$request->nom || !$request->equipe || !$request->salle) {
            return response()->json([
                'status' => false,
                'message' => 'Veuillez renseigner nom, equipe et salle.',
            ]);
        }

        if (!$request->collationId && !$request->repasId) {
            return response()->json([
                'status' => false,
                'message' => 'Veuillez choisir au moins un plat ou une collation.',
            ]);
        }

        if ($request->collationId && !Collation::where('id', $request->collationId)->exists()) {
            return response()->json([
                'status' => false,
                'message' => 'Collation invalide.',
            ]);
        }

        if ($request->repasId && !Repa::where('id', $request->repasId)->exists()) {
            return response()->json([
                'status' => false,
                'message' => 'Plat invalide.',
            ]);
        }

        $salle = null;
        if (is_numeric($request->salle)) {
            $salle = Salle::find((int) $request->salle);
        }
        if (!$salle) {
            $salle = Salle::where('libelle', $request->salle)->first();
        }
        if (!$salle) {
            $salle = $user->etudiant->getEquipe()->currentSalle();
            if ($salle) {
                $salle = Salle::find($salle->id);
            }
        }

        if (!$salle) {
            return response()->json([
                'status' => false,
                'message' => 'Aucune salle associee a votre equipe.',
            ]);
        }

        $hasRepaId = Schema::hasColumn('commandes', 'repa_id');
        $hasParticipantNom = Schema::hasColumn('commandes', 'participant_nom');
        $hasEquipeNom = Schema::hasColumn('commandes', 'equipe_nom');
        $hasSalleNom = Schema::hasColumn('commandes', 'salle_nom');

        if (!$hasRepaId && !$request->collationId) {
            return response()->json([
                'status' => false,
                'message' => 'Base non migree: choisissez une collation ou appliquez les migrations.',
            ], 422);
        }

        $payload = [
            'etudiant_id' => $user->etudiant->id,
            'salle_id' => $salle->id,
            'collation_id' => $request->collationId,
        ];

        if ($hasRepaId) {
            $payload['repa_id'] = $request->repasId;
        }
        if ($hasParticipantNom) {
            $payload['participant_nom'] = trim($request->nom);
        }
        if ($hasEquipeNom) {
            $payload['equipe_nom'] = trim($request->equipe);
        }
        if ($hasSalleNom) {
            $payload['salle_nom'] = trim($request->salle);
        }

        try {
            Commande::create($payload);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Erreur serveur pendant lenregistrement de la commande.',
                'error' => app()->environment('local') ? $e->getMessage() : null,
            ], 500);
        }

        return response()->json([
            'status' => true,
            'message' => 'ok',
        ]);
    }
}
