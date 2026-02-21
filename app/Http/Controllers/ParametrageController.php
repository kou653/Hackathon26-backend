<?php

namespace App\Http\Controllers;

use App\Models\Hackaton as ModelsHackaton;
use App\Models\Niveau as ModelsNiveau;
use App\Models\Salle as ModelsSalle;
use App\Models\Restauration;
use App\Models\Collation;
use App\Models\Etudiant;
use App\Models\RepSalle;
use App\Models\Question;
use App\Models\Response;
use App\Models\QsessionResponse;
use App\Models\Commande;
use App\Models\Niveau;
use App\Models\Classe;
use App\Models\Equipe;
use App\Models\Salle;
use App\Models\Repa;
use App\Models\Quiz;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;


use Illuminate\Http\Request;

class ParametrageController extends Controller
{

    // ----- HACKATHON TAB ---------- HACKATHON TAB ---------- HACKATHON TAB ---------- HACKATHON TAB ----- //
    public function renderhackathon()
    {
        $data = [
            'data' => ModelsHackaton::orderBy('created_at', 'DESC')->get(),
        ];

        $response = [
            'status' => true,
            'data' => $data,
        ];

        return response()->json($response);
    }

    public function createhackathon(Request $request)
    {
        if (!$request->pco_1 || !$request->pco_2 || !$request->annee) {
            $response = [
                'status' => false,
                'message' => "Remplissez tout les champs correctement",
            ];
        } else {
            if (ModelsHackaton::where('annee', $request->annee)->first()) {

                $response = [
                    'status' => false,
                    'message' => "Un hackathon est déja crée pour cette année",
                ];

            } else {

                ModelsHackaton::create([
                    'pco_1' => $request->pco_1,
                    'pco_2' => $request->pco_2,
                    'annee' => $request->annee,
                ]);

                $response = [
                    'status' => true,
                    'message' => "Hackathon crée avec succès",
                ];
            }
        }

        return response()->json($response);
    }

    public function tooglehackathon(Request $request)
    {
        if (!$request->hackathonId) {
            $response = [
                'status' => false,
                'message' => "Fournissez l'id de l'hackathon",
            ];
        } else {

            $hackaton = ModelsHackaton::find($request->hackathonId);
            $hackaton->inscription = !$hackaton->inscription;
            $hackaton->save();

            if ($hackaton->inscription == 1) {
                ModelsHackaton::where('inscription', 1)->where('id', '!=', $hackaton->id)->update(['inscription' => 0]);
            }

            $response = [
                'status' => true,
                'message' => $hackaton->inscription == 1 ? "Hackathon activé" : "Hackathon desactivé",
            ];
        }

        return response()->json($response);
    }

    // --------------------------------------------------------------------------------------------- //

    // ----- CLASSES TAB ---------- CLASSES TAB ---------- CLASSES TAB ---------- CLASSES TAB ----- //
    public function renderclasse(Request $request)
    {
        $classesQuery = Classe::orderBy('created_at', 'DESC')->with('niveau');

        $niveauId = $request->input('niveauId') ?? $request->input('niveau_id');
        if ($niveauId !== '' && $niveauId !== null) {
            $classesQuery->where('niveau_id', $niveauId);
        }

        $classes = $classesQuery->get();
        $data = [
            'niveaux' => ModelsNiveau::all(),
            'classes' => $classes,
            'classes_count' => $classes->count(),
            'niveau_id_filter' => $niveauId !== '' && $niveauId !== null ? (int) $niveauId : null,
        ];

        $response = [
            'status' => true,
            'data' => $data,
        ];

        return response()->json($response);
    }

    public function createclasse(Request $request)
    {
        if (!$request->libelle || !$request->niveau_id) {
            $response = [
                'status' => false,
                'message' => "Remplissez tout les champs correctement",
            ];
        } else {
            Classe::create([
                'libelle' => $request->libelle,
                'niveau_id' => $request->niveau_id,
                'esatic' => $request->input('isEsatic', 1),
            ]);

            $response = [
                'status' => true,
                'message' => "Classe crée avec succès",
            ];
        }

        return response()->json($response);
    }

    public function updateclasse(Request $request)
    {
        $classe = Classe::find($request->classeId);

        if (!$classe) {
            $response = [
                'status' => false,
                'message' => "Classe non trouvée",
            ];
        } else {

            if ($request->libelle)
                $classe->libelle = $request->libelle;
            if ($request->niveau_id)
                $classe->niveau_id = $request->niveau_id;
            if ($request->has('isEsatic'))
                $classe->esatic = $request->boolean('isEsatic');

            $classe->save();

            $response = [
                'status' => true,
                'message' => "Classe modifiée avec succès",
            ];
        }

        return response()->json($response);
    }

    public function deleteclasse(Request $request)
    {
        $classe = Classe::find($request->classeId);

        if (!$classe) {
            $response = [
                'status' => false,
                'message' => "Classe non trouvée",
            ];
        } else {
            $classe->delete();
            $response = [
                'status' => true,
                'message' => "Classe supprimée avec succès",
            ];
        }

        return response()->json($response);
    }

    // --------------------------------------------------------------------------------------------- //
    // ----- SALLES TAB ---------- SALLES TAB ---------- SALLES TAB ---------- SALLES TAB ----- //


    public function rendersalle()
    {
        $data = [
            'salles' => ModelsSalle::orderBy('created_at', 'DESC')->get(),
        ];

        $response = [
            'status' => true,
            'data' => $data,
        ];

        return response()->json($response);
    }

    public function createsalle(Request $request)
    {
        if (!$request->libelle || !$request->nb_equipe) {
            $response = [
                'status' => false,
                'message' => "Remplissez tout les champs correctement",
            ];
        } else {
            ModelsSalle::create([
                'libelle' => $request->libelle,
                'nb_equipe' => $request->nb_equipe,
            ]);

            $response = [
                'status' => true,
                'message' => "Salle crée avec succès",
            ];
        }

        return response()->json($response);
    }


    /*
    {
        'salleId' => id de la salle
        'libelle' => nouveau libellé
        'nb_equipe' => nouveau nombre max d'équipe dans la salle
    }
    */
    public function updatesalle(Request $request)
    {
        $salle = ModelsSalle::find($request->salleId);

        if (!$salle) {
            $response = [
                'status' => false,
                'message' => "Salle non trouvée",
            ];
        } else {

            if ($request->libelle)
                $salle->libelle = $request->libelle;
            if ($request->nb_equipe)
                $salle->nb_equipe = $request->nb_equipe;

            $salle->save();

            $response = [
                'status' => true,
                'message' => "Salle modifiée avec succès",
            ];
        }

        return response()->json($response);
    }

    public function deletesalle(Request $request)
    {
        $salle = ModelsSalle::find($request->salleId);

        if (!$salle) {
            $response = [
                'status' => false,
                'message' => "Salle non trouvée",
            ];
        } else {
            $salle->delete();
            $response = [
                'status' => true,
                'message' => "Salle supprimée avec succès",
            ];
        }

        return response()->json($response);
    }


    // --------------------------------------------------------------------------------------------- //
    // ----- REPARTITIONS TAB ---------- REPARTITIONS TAB ---------- REPARTITIONS TAB ---------- REPARTITIONS TAB ----- //

    public function renderrepartition()
    {

        $hackaton = ModelsHackaton::where('inscription', 1)->first();
        $reps = RepSalle::where('hackaton_id', $hackaton->id)
            ->orderBy('created_at', 'DESC')->get();

        foreach ($reps as $rep) {
            $rep->salle = Salle::find($rep->salle_id);
            $rep->equipe = Equipe::find($rep->equipe_id);
        }

        $data = [
            'equipes' => Equipe::where('statut', 1)
                ->where('hackaton_id', $hackaton->id)->get(),

            'salles' => ModelsSalle::all(),

            'repartitions' => $reps
        ];

        $response = [
            'status' => true,
            'data' => $data,
        ];

        return response()->json($response);
    }

    /*
    {
        'salleId' => id de la salle
        'equipeId' => id de l'equipe
    }
    */
    public function createrepartition(Request $request)
    {

        if (!$request->salleId || !$request->equipeId) {
            $response = [
                'status' => false,
                'message' => "Remplissez tout les champs correctement",
            ];
        } else {
            $salle = Salle::find($request->salleId);
            if ($salle->canRecieve()) {
                $hackaton = ModelsHackaton::where('inscription', 1)->first();

                RepSalle::create([
                    'equipe_id' => $request->equipeId,
                    'salle_id' => $request->salleId,
                    'hackaton_id' => $hackaton->id
                ]);

                $response = [
                    'status' => true,
                    'message' => "Salle crée avec succès",
                ];

            } else {

                $response = [
                    'status' => true,
                    'message' => "Nombre maximum de salle atteint",
                ];

            }
        }

        return response()->json($response);

    }

    /*
    {
        'repartitionId' => id de la repartition à supprimer
    }
    */
    public function deleterepartition(Request $request)
    {
        if (!$request->repartitionId) {

            $response = [
                'status' => false,
                'message' => "Remplissez tout les champs correctement",
            ];

        } else {
            $rep = RepSalle::find($request->repartitionId);
            if (!$rep) {

                $response = [
                    'status' => false,
                    'message' => "Repartition non trouvée",
                ];

            } else {

                $rep->delete();
                $response = [
                    'status' => true,
                    'message' => "Repartition supprimée avec succès",
                ];

            }
        }

        return response()->json($response);
    }


    // --------------------------------------------------------------------------------------------- //
    // ----- RESTAURATIONS TAB ---------- RESTAURATIONS TAB ---------- RESTAURATIONS TAB ---------- RESTAURATIONS TAB ----- //


    public function renderrestauration()
    {
        $data = [
            'repas' => Repa::orderBy('created_at', 'DESC')->get(),
            'collations' => Collation::orderBy('created_at', 'DESC')->get()
        ];

        $response = [
            'data' => $data,
            'status' => true,
        ];

        return response()->json($response);
    }

    /*
    {
        'libelle' => libellé du repas
    }
    */
    public function createrepas(Request $request)
    {

        if (!$request->libelle) {
            $response = [
                'status' => false,
                'message' => "Remplissez tout les champs correctement",
            ];
        } else {

            Repa::create([
                'libelle' => $request->libelle,
            ]);

            $response = [
                'status' => true,
                'message' => "Repas crée avec succès",
            ];

        }

        return response()->json($response);

    }

    /*
    {
        'libelle' => libellé de la collation
    }
    */
    public function createcollation(Request $request)
    {

        if (!$request->libelle) {
            $response = [
                'status' => false,
                'message' => "Remplissez tout les champs correctement",
            ];
        } else {

            Collation::create([
                'libelle' => $request->libelle,
            ]);

            $response = [
                'status' => true,
                'message' => "Collation créee avec succès",
            ];

        }

        return response()->json($response);

    }

    /*
    {
        'repasId' => id du repas à supprimer
    }
    */
    public function deleterepas(Request $request)
    {
        if (!$request->repasId) {

            $response = [
                'status' => false,
                'message' => "Remplissez tout les champs correctement",
            ];

        } else {
            $rep = Repa::find($request->repasId);
            if (!$rep) {

                $response = [
                    'status' => false,
                    'message' => "Repas non trouvé",
                ];

            } else {

                $rep->delete();
                $response = [
                    'status' => true,
                    'message' => "Repas supprimé avec succès",
                ];

            }
        }

        return response()->json($response);
    }

    /*
    {
        'collationId' => id de la collation à supprimer
    }
    */
    public function deletecollation(Request $request)
    {
        if (!$request->collationId) {

            $response = [
                'status' => false,
                'message' => "Remplissez tout les champs correctement",
            ];

        } else {
            $rep = Collation::find($request->collationId);
            if (!$rep) {

                $response = [
                    'status' => false,
                    'message' => "Collation non trouvée",
                ];

            } else {

                $rep->delete();
                $response = [
                    'status' => true,
                    'message' => "Collation supprimée avec succès",
                ];

            }
        }

        return response()->json($response);
    }

    // --------------------------------------------------------------------------------------------- //
    // ----- PRESELECTIONS TAB ---------- PRESELECTIONS TAB ---------- PRESELECTIONS TAB ---------- PRESELECTIONS TAB ----- //

    /*
    {
        'niveauId' => id du niveau
    }
    */
    public function renderpreselection(Request $request)
    {
        $niveau = Niveau::where('id', $request->niveauId)->where('quiz_available', 1)->first();
        if (!$niveau) {

            $response = [
                'message' => "Quiz non permis pour ce niveau",
                'status' => false,
            ];

        } else {

            $quiz = Quiz::where('niveau_id', $request->niveauId)->first();
            $data = [
                'questions' => Question::with('responses')->where('quiz_id', $quiz->id)->orderBy('created_at', 'desc')->get(),
                'quiz_score' => $quiz->score,
                'quiz_state' => $quiz->state,
            ];

            $response = [
                'data' => $data,
                'status' => true,
            ];

        }

        return response()->json($response);
    }

    /*
    {
        'niveauId' => id du niveau,
        'question' => la question à enregistrer
    }
    */
    public function createquestion(Request $request)
    {

        if (!$request->niveauId || !$request->question) {

            $response = [
                'message' => "Remplissez tout les champs correctement",
                'status' => false,
            ];

        } else {

            Question::create([
                'quiz_id' => Quiz::where('niveau_id', $request->niveauId)->first()->id,
                'content' => $request->question,
            ]);

            $response = [
                'message' => "",
                'status' => true,
            ];

        }

        return response()->json($response);

    }

    /*
    {
        'questionId' => id de la question,
        'question' => nouvau contenu
    }
    */
    public function updatequestion(Request $request)
    {
        if (!$request->questionId || !$request->question) {

            $response = [
                'status' => false,
                'message' => "Remplissez tout les champs correctement",
            ];

        } else {
            $question = Question::find($request->questionId);
            if (!$question) {

                $response = [
                    'status' => false,
                    'message' => "Question non trouvée",
                ];

            } else {

                $question->content = $request->question;
                $question->save();

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
        'questionId' => id de la question
    }
    */
    public function deletequestion(Request $request)
    {
        if (!$request->questionId) {

            $response = [
                'status' => false,
                'message' => "Remplissez tout les champs correctement",
            ];

        } else {
            $question = Question::find($request->questionId);
            if (!$question) {

                $response = [
                    'status' => false,
                    'message' => "Question non trouvée",
                ];

            } else {

                $quiz = Quiz::find($question->quiz_id);
                foreach ($question->responses as $res) {
                    $quiz->score -= $res->score;
                    $quiz->save();
                }
                $question->delete();

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
        'niveauId' => id du niveau,
        'questionId' => id de la question,
        'response' => la reponse à enregister,
        'score' => le score associé à la reponse
    }
    */
    public function createresponse(Request $request)
    {
        if (!$request->niveauId || !$request->questionId || !$request->response) {

            $response = [
                'message' => "Remplissez tout les champs correctement",
                'status' => false,
            ];

        } else {


            $response = Response::create([
                'question_id' => $request->questionId,
                'content' => $request->response,
                'score' => $request->score,
            ]);

            $quiz = Quiz::where('niveau_id', $request->niveauId)->first();
            $quiz->score += $request->score;
            $quiz->save();

            foreach ($quiz->qsessions as $qsession) {
                QsessionResponse::firstOrCreate(
                    [
                        'qsession_id' => $qsession->id,
                        'response_id' => $response->id,
                        'question_id' => $response->question_id
                    ],
                    [
                        'score' => $response->score,
                        'state' => 0
                    ]
                );
            }

            $response = [
                'message' => "ok",
                'status' => true,
            ];

        }

        return response()->json($response);
    }

    /*
    {
        'responseId' => id de la reponse
    }
    */
    public function deleteresponse(Request $request)
    {
        if (!$request->responseId) {

            $response = [
                'status' => false,
                'message' => "Remplissez tout les champs correctement",
            ];

        } else {
            $rep = Response::find($request->responseId);
            if (!$rep) {

                $response = [
                    'status' => false,
                    'message' => "Réponse non trouvée",
                ];

            } else {

                $quiz = Quiz::find($rep->question->quiz_id);
                $quiz->score -= $rep->score;
                $quiz->save();

                $rep->delete();
                $response = [
                    'status' => true,
                    'message' => "Réponse supprimée avec succès",
                ];

            }
        }

        return response()->json($response);
    }

    /*
    {
        'quizId' => id du quiz
    }
    */
    public function tooglequiz(Request $request)
    {

        if (!$request->quizId) {

            $response = [
                'status' => false,
                'message' => "Remplissez tout les champs correctement",
            ];

        } else {
            $quiz = Quiz::find($request->quizId);
            if (!$quiz) {

                $response = [
                    'status' => false,
                    'message' => "Quiz non trouvé",
                ];

            } else {

                $quiz->state = !$quiz->state;
                $quiz->save();
                $response = [
                    'status' => true,
                    'message' => "ok",
                ];

            }
        }

        return response()->json($response);
    }

    // --------------------------------------------------------------------------------------------- //
    // ----- RESTAURATIONS TAB ---------- RESTAURATIONS TAB ---------- RESTAURATIONS TAB ---------- RESTAURATIONS TAB ----- //


    public function rendercommandes()
    {
        $data = [
            "commandes" => Commande::with('collation')->get()
        ];

        $response = [
            'status' => true,
            'data' => $data,
        ];

        return response()->json($response);

    }

    public function resetcommandes()
    {
        Commande::truncate();

        $response = [
            'status' => true,
            'message' => "ok",
        ];

        return response()->json($response);

    }

    /*
    {
        'qrcodeValue' => valeur du qrcode
    }
    */
    public function restaurationsoumission(Request $request)
    {
        $codeDecrypte = Crypt::decryptString($request->qrcodeValue);

        $etudiant = Etudiant::where('matricule', $codeDecrypte)->first();

        if (Repa::latest()->first()) {

            $statut = Restauration::where('etudiant_id', $etudiant->id)
                ->where('repa_id', Repa::latest()->first()->id)
                ->where('hackaton_id', ModelsHackaton::where('inscription', 1)->first()->id)
                ->first();

            if (!$statut) {
                Restauration::create([
                    'etudiant_id' => $etudiant->id,
                    'repa_id' => Repa::latest()->first()->id,
                    'hackaton_id' => ModelsHackaton::where('inscription', 1)->first()->id
                ]);

                $response = [
                    'status' => true,
                    'message' => "Bon appetit",
                ];

            } else {

                $response = [
                    'status' => false,
                    'message' => "Déjà restauré",
                ];
            }
        } else {

            $response = [
                'status' => false,
                'message' => "Enregistrer un repas",
            ];

        }

        return response()->json($response);

    }

    public function renderallrepas()
    {
        $repas = Repa::orderBy('created_at', 'DESC')->get();
        $hackaton = ModelsHackaton::where('inscription', 1)->first();

        $nb_participants = DB::table('participants')
            ->join('equipes', 'equipes.id', '=', 'participants.equipe_id')
            ->where('equipes.hackaton_id', $hackaton->id)
            ->where('equipes.statut', 1)
            ->get()
            ->count();

        foreach ($repas as $repa) {
            $repa->nbEaten = $repa->restauration()->count();
        }

        $data = [
            'repas' => $repas,
            'nbEaters' => $nb_participants
        ];

        $response = [
            'status' => true,
            'data' => $data,
        ];

        return response()->json($response);

    }
}
