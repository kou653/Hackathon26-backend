<?php

namespace App\Http\Controllers;

use App\Models\Collation;
use App\Models\Commande;
use App\Models\Repa;
use App\Models\Salle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class ParticipantController extends Controller
{
    public function renderprestauration()
    {
        $user = Auth::user();
        $qrcodeValue = Crypt::encryptString($user->etudiant->matricule);
        $commande = $user->etudiant->Commande();

        $data = [
            'qrcodeValue' => $qrcodeValue,
            'repas' => Repa::orderBy('created_at', 'DESC')->get(),
            'collations' => Collation::orderBy('created_at', 'DESC')->get(),
            'salles' => Salle::orderBy('libelle')->get(),
        ];

        if ($commande) {
            $data['hasOrdered'] = true;
            $data['commande'] = Commande::with(['repa', 'collation'])->find($commande->id);
        } else {
            $data['hasOrdered'] = false;
        }

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

        if ($user->etudiant->Commande()) {
            return response()->json([
                'status' => false,
                'message' => 'Vous avez deja passe une commande.',
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

        Commande::create([
            'etudiant_id' => $user->etudiant->id,
            'salle_id' => $salle->id,
            'repa_id' => $request->repasId,
            'collation_id' => $request->collationId,
            'participant_nom' => trim($request->nom),
            'equipe_nom' => trim($request->equipe),
            'salle_nom' => trim($request->salle),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'ok',
        ]);
    }
}
