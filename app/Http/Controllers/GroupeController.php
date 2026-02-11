<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Hackaton as ModelsHackaton;
use App\Models\Qsession;
use App\Models\Classe;
use App\Models\Equipe;
use App\Models\Niveau;
use App\Models\Quiz;



class GroupeController extends Controller
{
    //
    public function rendergroupe(Request $request)
    {
        $hackaton = ModelsHackaton::where('inscription', 1)->first();
        if (!$hackaton) {
            $data = [
                'equipes' => [],
                'niveaux' => Niveau::all()
            ];
        } else {
            $equipes = Equipe::with('participants.etudiant', 'niveau', 'qsession.quiz')->where('hackaton_id', $hackaton->id)
                ->where('niveau_id', $request->niveauId)
                ->where('statut', $request->statut)
                ->get();

            // foreach ($equipes as $eq) {
            //     if (Classe::where('libelle', $eq->participants[0]->etudiant->classe)->first()->esatic == 0)
            //         $eq->is_extern = true;
            //     else
            //         $eq->is_extern = false;
            // }
            $data = [
                'niveaux' => Niveau::all(),
                'equipes' => $equipes,
            ];
        }

        $response = [
            'status' => true,
            'data' => $data,
        ];

        return response()->json($response);
    }

    /*
    {
        'equipeId' => id de l'équipe
    }
    */
    public function toogleequipe(Request $request)
    {

        if (!$request->equipeId) {

            $response = [
                'status' => false,
                'message' => "Remplissez tout les champs correctement",
            ];

        } else {
            $equipe = Equipe::find($request->equipeId);
            if (!$equipe) {

                $response = [
                    'status' => false,
                    'message' => "Equipe non trouvée",
                ];

            } else {

                $equipe->statut = !$equipe->statut;
                $equipe->save();

                $response = [
                    'status' => true,
                    'message' => "ok",
                ];

            }
        }

        return response()->json($response);
    }

    /*
    {
        'equipeId' => id de l'equipe dont on veut réinitialiser le quiz
    }
    */
    public function resetquiz(Request $request)
    {
        if (!$request->equipeId) {

            $response = [
                'status' => false,
                'message' => "Remplissez tout les champs correctement",
            ];

        } else {
            $session = Qsession::where('equipe_id', $request->equipeId)->first();

            if (!$session) {

                $response = [
                    'status' => false,
                    'message' => "Session non trouvé",
                ];

            } else {

                $session->score = 0;
                $session->state = 0;
                $session->save();

                $response = [
                    'status' => true,
                    'message' => "ok",
                ];
            }
        }
        return response()->json($response);

    }

    /*
    {
        'niveauId' => id du niveau dont on veut faire la sélection automatique,
        'nbreEquipe' => nombre d'equipe à sélectionner
    }
    */
    public function autoselectgroupe(Request $request)
    {
        if (!$request->niveauId) {

            $response = [
                'status' => false,
                'message' => "Remplissez tout les champs correctement",
            ];

        } else {
            $quiz = Quiz::where('niveau_id', $request->niveauId)->first();

            if (!$quiz) {

                $response = [
                    'status' => false,
                    'message' => "Quiz non trouvé",
                ];

            } else {
                $sessions = Qsession::where('quiz_id', $quiz->id)->orderBy('score', 'desc')->get();

                if ($request->nbreEquipe == 0) {
                    $sessions = Qsession::where('quiz_id', $quiz->id)->get();

                    foreach ($sessions as $session) {
                        $e = $session->equipe;
                        $e->statut = 0;
                        $e->save();
                    }

                } else {
                    if ($request->nbreEquipe >= sizeof($sessions)) {
                        foreach ($sessions as $session) {
                            $session->state = 1;
                            $session->save();

                            $e = $session->equipe;
                            $e->statut = 1;
                            $e->save();
                        }
                    } else {
                        for ($i = 0; $i < $request->nbreEquipe; $i++) {
                            $e = $sessions[$i]->equipe;
                            $e->statut = 1;
                            $e->save();
                        }
                    }
                }


                $response = [
                    'status' => true,
                    'message' => "ok",
                ];
            }
        }
        return response()->json($response);
    }
}
