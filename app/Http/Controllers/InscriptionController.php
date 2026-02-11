<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Imports\MatriculesImport;
use Exception;
use App\Models\Equipe;
use App\Models\Etudiant;
use App\Models\Hackaton;
use App\Models\Participant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;

use App\Models\Matricule;
use App\Models\Qsession;
use Livewire\Component;
use App\Models\Classe;
use App\Models\Niveau;
use App\Models\Quiz;

use Illuminate\Support\Facades\Hash;

class InscriptionController extends Controller
{

    // Log In
    public function login(Request $request)
    {
        $credentials = request(['email', 'password']);
        if (Auth::attempt($credentials)) {

            $user = $request->user();


            $token = $user->createToken('Personal Access Token')->plainTextToken;
            $membres = [];

            $isparticipant = Auth::user()->etudiant ? true : false;
            if ($isparticipant) {
                $user->niveau = $user->etudiant->getEquipe()->niveau;
                $user->team_qualified = $user->etudiant->getEquipe()->statut;
                foreach ($user->etudiant->getEquipe()->participants as $participant) {
                    if ($participant->chef == 1) {
                        $participant->etudiant["chef"] = 1;
                    } else {
                        $participant->etudiant["chef"] = 0;
                    }
                    $participant->etudiant["email"] = $participant->etudiant->user->email;
                    array_push($membres, $participant->etudiant);
                }
            }

            $data = [
                'role' => $isparticipant ? "participant" : "admin",
                'message' => 'Vous êtes connecté(e)',
                'accessToken' => $token,
                'equipe' => $membres,
                'status' => true,
                'user' => $user,
            ];

            $response = [
                'data' => $data,
                'status' => true
            ];

            // if ($isparticipant && $user->etudiant->getEquipe()->statut == 0) {
            //     $response = [
            //         'status' => false,
            //         'message' => 'Désolé, votre équipe n\'est pas sélectionnée'
            //     ];
            // }


        } else {
            $response = [
                'status' => false,
                'message' => 'Email ou mot de pass incorrect'
            ];
        }

        return response()->json($response);

    }

    // Log Out
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        $response = [
            'message' => 'Successfully logged out',
            'status' => true
        ];

        return response()->json($response);
    }


    public function inscriptionstate()
    {
        $hackaton = Hackaton::where('inscription', 1)->first();

        $canrecord = Hackaton::where('inscription', 1)->first()->CanRecord();

        $data = [
            'hackaton_inscription' => $hackaton->inscription,
            'canrecord' => $canrecord
        ];

        $response = [
            'data' => $data,
            'status' => true
        ];

        return response()->json($response);
    }

    public function inscriptionterminer()
    {
        $hackaton = Hackaton::where('inscription', 1)->first();


        $data = [
            'hackaton_inscription' => $hackaton->inscription
        ];

        $response = [
            'data' => $data,
            'statut' => true
        ];

        return response()->json($response);
    }

    public function getRandomInt($n)
    {
        $characters = '0123456789';
        $randomString = '';

        for ($i = 0; $i < $n; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }

        return $randomString;
    }

    public function getRandomString($n)
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';

        for ($i = 0; $i < $n; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }

        return $randomString;
    }

    public function participants_enregistrement_createEquipe(Request $request)
    {
        if (
            isset($request->nom_groupe) and
            isset($request->photo_groupe) and
            isset($request->niveau) and
            isset($request->email_chef) and
            isset($request->nom_chef) and
            isset($request->prenom_chef) and
            isset($request->genre_chef) and
            isset($request->classe_chef) and
            isset($request->esatic) and
            isset($request->email_m2) and
            isset($request->nom_m2) and
            isset($request->prenom_m2) and
            isset($request->genre_m2) and
            isset($request->classe_m2) and
            isset($request->email_m3) and
            isset($request->nom_m3) and
            isset($request->prenom_m3) and
            isset($request->genre_m3) and
            isset($request->classe_m3)
        ) {
            $errorEmail = false;
            $errorMatricule = false;
            $matricule_chef = $request->matricule_chef;
            $matricule_m2 = $request->matricule_m2;
            $matricule_m3 = $request->matricule_m3;

            if ($request->esatic == 0) {
                $matricule_chef = $this->getRandomInt(2) . "-" . $request->classe_chef . $this->getRandomInt(4) . $this->getRandomString(2);
                $matricule_m2 = $this->getRandomInt(2) . "-" . $request->classe_m2 . $this->getRandomInt(4) . $this->getRandomString(2);
                $matricule_m3 = $this->getRandomInt(2) . "-" . $request->classe_m3 . $this->getRandomInt(4) . $this->getRandomString(2);
            }

            if (
                $request->email_chef == $request->email_m2 or
                $request->email_chef == $request->email_m3 or
                $request->email_m2 == $request->email_m3
            ) {
                $errorEmail = true;
            }

            if ($request->esatic == 0) {
                if (
                    $matricule_chef == $matricule_m2 or
                    $matricule_chef == $matricule_m3 or
                    $matricule_m2 == $matricule_m3
                ) {
                    $errorMatricule = true;
                }
            }

            if (!$errorEmail and !$errorMatricule) {

                // recuperation de l'hackaton

                $hackaton = Hackaton::where('inscription', 1)->first();


                // creation de l'équipe
                if (Equipe::where('nom', $request->nom_groupe)->first()) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Cette équipe existe déjà'
                    ]);
                }
                if (
                    User::where('email', $request->email_chef)
                        ->orWhere('email', $request->email_m2)
                        ->orWhere('email', $request->email_m3)
                        ->first()
                ) {
                    return response()->json([
                        'status' => false,
                        'message' => "L'un des utilisateurs existe déjà"
                    ]);
                }
                $equipe = Equipe::create([
                    'nom' => $request->nom_groupe,
                    'logo' => $request->photo_groupe,
                    'niveau_id' => $request->niveau,
                    'hackaton_id' => $hackaton->id
                ]);

                if (Niveau::find($request->niveau)->quiz_available == 1) {
                    Qsession::create([
                        'quiz_id' => Quiz::where('niveau_id', $request->niveau)->first()->id,
                        'equipe_id' => $equipe->id
                    ]); 
                }

                // creation du chef
                $user1 = User::create([
                    'name' => trim($matricule_chef),
                    'email' => $request->email_chef,
                    'password' => Hash::make("NotreSDI25@TH12345")
                ]);

                $classe_chef_libelle = $request->esatic == 1
                    ? (Classe::find($request->classe_chef)->libelle ?? $request->classe_chef)
                    : $request->classe_chef;

                $etudiant1 = Etudiant::create([
                    'nom' => strtoupper($request->nom_chef),
                    'prenom' => strtoupper($request->prenom_chef),
                    'matricule' => trim($matricule_chef),
                    'genre' => $request->genre_chef,
                    'classe' => $classe_chef_libelle,
                    'user_id' => $user1->id
                ]);


                // creation du participant 2 
                $user2 = User::create([
                    'name' => trim($matricule_m2),
                    'email' => $request->email_m2,
                    'password' => Hash::make("NotreSDI25@TH12345")
                ]);

                $classe_m2_libelle = $request->esatic == 1
                    ? (Classe::find($request->classe_m2)->libelle ?? $request->classe_m2)
                    : $request->classe_m2;

                $etudiant2 = Etudiant::create([
                    'nom' => strtoupper($request->nom_m2),
                    'prenom' => strtoupper($request->prenom_m2),
                    'matricule' => trim($matricule_m2),
                    'genre' => $request->genre_m2,
                    'classe' => $classe_m2_libelle,
                    'user_id' => $user2->id
                ]);


                // creation du participant 3
                $user3 = User::create([
                    'name' => trim($matricule_m3),
                    'email' => $request->email_m3,
                    'password' => Hash::make("NotreSDI25@TH12345")
                ]);

                $classe_m3_libelle = $request->esatic == 1
                    ? (Classe::find($request->classe_m3)->libelle ?? $request->classe_m3)
                    : $request->classe_m3;

                $etudiant3 = Etudiant::create([
                    'nom' => strtoupper($request->nom_m3),
                    'prenom' => strtoupper($request->prenom_m3),
                    'matricule' => trim($matricule_m3),
                    'genre' => $request->genre_m3,
                    'classe' => $classe_m3_libelle,
                    'user_id' => $user3->id
                ]);

                // enregistrement des participants

                Participant::create([
                    'chef' => true,
                    'etudiant_id' => $etudiant1->id,
                    'equipe_id' => $equipe->id,
                    'hackaton_id' => $hackaton->id
                ]);

                Participant::create([
                    'etudiant_id' => $etudiant2->id,
                    'equipe_id' => $equipe->id,
                    'hackaton_id' => $hackaton->id
                ]);

                Participant::create([
                    'etudiant_id' => $etudiant3->id,
                    'equipe_id' => $equipe->id,
                    'hackaton_id' => $hackaton->id
                ]);

                $response = [
                    "messsage" => "Enregiistrement effectué !",
                    "status" => true
                ];

                return response()->json($response);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Remplissez tout les chapms correctement'
            ]);
        }
    }

    public function enregistrement_render(Request $request)
{
    $data = [
        'niveaux' => Niveau::with([
            'classes' => function ($query) use ($request) {
                $query->where('esatic', $request->esatic);
            }
        ])->get(),
    ];

    return response()->json([
        'data' => $data,
        'status' => true
    ]);
}

}
