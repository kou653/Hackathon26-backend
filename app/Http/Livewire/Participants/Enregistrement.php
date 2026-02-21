<?php

namespace App\Http\Livewire\Participants;

use App\Models\Classe;
use App\Models\Equipe;
use App\Models\Etudiant;
use App\Models\Hackaton;
use App\Models\Matricule;
use App\Models\Niveau;
use App\Models\Participant;
use App\Models\User;
use App\Models\Qsession;
use App\Models\QsessionResponse;
use App\Models\Response as QuizResponse;
use App\Models\Quiz;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class Enregistrement extends Component
{
    // variables de groupe

    public $niveau = 0;
    public $nom_groupe;
    public $photo_groupe;

    public $esatic = 1;

    // variables relative au chef

    public $matricule_chef;
    public $nom_chef;
    public $prenom_chef;
    public $classe_chef = "c";
    public $email_chef;
    public $genre_chef = "g";


    // variables relatives au membre 2

    public $matricule_m2;
    public $nom_m2;
    public $prenom_m2;
    public $classe_m2 = "c";
    public $email_m2;
    public $genre_m2 = "g";
    // variables relatives au membre 3

    public $matricule_m3;
    public $nom_m3;
    public $prenom_m3;
    public $classe_m3 = "c";
    public $email_m3;
    public $genre_m3 = "g";

    public $errorEmail = false;
    public $errorMatricule = false;


    public function render()
    {
        return view('livewire.participants.enregistrement', [
            'niveaux' => $this->esatic == 1 ? Niveau::all() : Niveau::where('quiz_available', 0)->get(),
            'classes' => Classe::where('niveau_id', $this->niveau)->where('esatic', $this->esatic)->get()
        ]);
    }

    public function VerifEmail()
    {
        if (
            $this->email_chef == $this->email_m2 or
            $this->email_chef == $this->email_m3 or
            $this->email_m2 == $this->email_m3
        ) {
            $this->errorEmail = true;
        }
    }

    public function matInDb($mat){

        $mindb = Matricule::where('matricule', $mat)->first();
        if($mindb){
            if($mindb->state == 1){
                return true;
            }
            return false;
        }
    }

    public function VerifMatricule()
    {
        if (
            $this->matricule_chef == $this->matricule_m2 or
            $this->matricule_chef == $this->matricule_m3 or
            $this->matricule_m2 == $this->matricule_m3
        ) {
            $this->errorMatricule = true;
        }
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


    public function setMAtricule()
    {
        if ($this->esatic == 0) {
            $this->matricule_chef = $this->getRandomInt(2) . "-" . $this->classe_chef . $this->getRandomInt(4) . $this->getRandomString(2);
            $this->matricule_m2 = $this->getRandomInt(2) . "-" . $this->classe_m2 . $this->getRandomInt(4) . $this->getRandomString(2);
            $this->matricule_m3 = $this->getRandomInt(2) . "-" . $this->classe_m3 . $this->getRandomInt(4) . $this->getRandomString(2);
        }
    }

    public function resetInput()
    {
        $this->niveau = "1";
        $this->nom_groupe = "";
        $this->photo_groupe  = "";

        // variables relative au chef

        $this->matricule_chef = "";
        $this->nom_chef = "";
        $this->prenom_chef = "";
        $this->classe_chef = "c";
        $this->email_chef = "";

        $this->genre_chef = "g";
        $this->genre_m3 = "g";
        $this->genre_m2 = "g";


        // variables relatives au membre 2

        $this->matricule_m2 = "";
        $this->nom_m2 = "";
        $this->prenom_m2 = "";
        $this->classe_m2 = "c";
        $this->email_m2 = "";

        // variables relatives au membre 3

        $this->matricule_m3 = "";
        $this->nom_m3 = "";
        $this->prenom_m3 = "";
        $this->classe_m3 = "c";
        $this->email_m3 = "";
    }

    public function createEquipe()
    {

        $this->setMAtricule();

        $validate = $this->validate([
            'niveau' => 'required',
            'nom_groupe' => 'required',

            'matricule_chef' => 'required|min:8|unique:etudiants,matricule',
            'nom_chef' => 'required',
            'prenom_chef' => 'required',
            'classe_chef' => 'required',
            'email_chef' => 'required|email|unique:users,email',

            'matricule_m2' => 'required|min:8|unique:etudiants,matricule',
            'nom_m2' => 'required',
            'prenom_m2' => 'required',
            'classe_m2' => 'required',
            'email_m2' => 'required|email|email|unique:users,email',

            'genre_chef' => 'required',
            'genre_m2' => 'required',
            'genre_m3' => 'required',


            'matricule_m3' => 'required|min:8|unique:etudiants,matricule',
            'nom_m3' => 'required',
            'prenom_m3' => 'required',
            'classe_m3' => 'required',
            'email_m3' => 'required|email|email|unique:users,email'

        ]);

        // dd($validate);

        $this->errorEmail = false;

        $this->VerifEmail();
        $this->VerifMatricule();


        if (!$this->errorEmail and !$this->errorMatricule) {

            // recuperation de l'hackaton

            $hackaton = Hackaton::where('inscription', 1)->first();

            // creation de l'équipe
            $equipe = Equipe::create([
                'nom' => $this->nom_groupe,
                'logo' => $this->photo_groupe,
                'niveau_id' => $this->niveau,
                'hackaton_id' => $hackaton->id
            ]);


            if (Niveau::find($this->niveau)->quiz_available == 1) {
                $quiz = Quiz::where('niveau_id', $this->niveau)->first();
                $qsession = Qsession::create([
                    'quiz_id' => $quiz->id,
                    'equipe_id' => $equipe->id
                ]);

                // Initialize per-session responses so scoring works for new teams.
                $responses = QuizResponse::whereIn('question_id', $quiz->questions()->pluck('id'))->get();
                foreach ($responses as $responseModel) {
                    QsessionResponse::firstOrCreate(
                        [
                            'qsession_id' => $qsession->id,
                            'response_id' => $responseModel->id,
                            'question_id' => $responseModel->question_id
                        ],
                        [
                            'score' => $responseModel->score,
                            'state' => 0
                        ]
                    );
                }
            }
            // creation du participant 1

            $user1 = User::create([
                'name' => trim($this->matricule_chef),
                'email' => $this->email_chef,
                'password' => Hash::make("sdi23@TH12345")
            ]);


            $etudiant1 = Etudiant::create([
                'nom' => $this->nom_chef,
                'prenom' => $this->prenom_chef,
                'matricule' => trim($this->matricule_chef),
                'genre' => $this->genre_chef,
                'classe' => $this->esatic == 1 ? Classe::find($this->classe_chef)->libelle : $this->classe_chef,
                'user_id' => $user1->id
            ]);

            // creation du participant 2 

            $user2 = User::create([
                'name' => trim($this->matricule_m2),
                'email' => $this->email_m2,
                'password' => Hash::make("sdi23@TH12345")
            ]);

            $etudiant2 = Etudiant::create([
                'nom' => $this->nom_m2,
                'prenom' => $this->prenom_m2,
                'matricule' => trim($this->matricule_m2),
                'genre' => $this->genre_m2,
                'classe' => $this->esatic == 1 ? Classe::find($this->classe_m2)->libelle : $this->classe_m2,
                'user_id' => $user2->id
            ]);

            // creation du participant 3 

            $user3 = User::create([
                'name' => trim($this->matricule_m3),
                'email' => $this->email_m3,
                'password' => Hash::make("sdi23@TH12345")
            ]);

            $etudiant3 = Etudiant::create([
                'nom' => $this->nom_m3,
                'prenom' => $this->prenom_m3,
                'matricule' => trim($this->matricule_m3),
                'genre' => $this->genre_m3,
                'classe' => $this->esatic == 1 ? Classe::find($this->classe_m3)->libelle : $this->classe_m3,
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

            //  $this->resetInput();

            return redirect()->to('/inscription-terminer');
        }
    }
}
