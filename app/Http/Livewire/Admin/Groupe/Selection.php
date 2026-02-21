<?php

namespace App\Http\Livewire\Admin\Groupe;

use App\Models\Equipe;
use App\Models\Hackaton;
use App\Models\Niveau;
use App\Models\Quiz;
use App\Models\Qsession;
use Livewire\Component;
use Livewire\WithPagination;

class Selection extends Component
{

    use WithPagination;

    public $niveauselect = "1";
    public $statut;
    public $afficher = '';

    public $nb_team = 1;
    public $manualScores = [];

    public function render()
    {

        $hackaton = Hackaton::where('inscription', 1)->first();

        return view('livewire.admin.groupe.selection', [
            'equipes' => Equipe::where('hackaton_id', $hackaton->id)
                ->where('niveau_id', 'LIKE', "%{$this->niveauselect}%")
                ->where('statut', 'LIKE', "%{$this->afficher}%")
                ->orderBy('created_at', 'DESC')->paginate(10),

            'niveaux' => Niveau::all(),

            'niveau' => Niveau::where('id', 'LIKE', "%{$this->niveauselect}%")->first()
        ]);
    }

    public function setScore(int $equipeId)
    {
        $equipe = Equipe::with('qsession')->find($equipeId);
        if (!$equipe || !$equipe->qsession) {
            return;
        }

        if (!array_key_exists($equipeId, $this->manualScores)) {
            return;
        }

        $value = (int) $this->manualScores[$equipeId];
        if ($value < 0) {
            $value = 0;
        }

        $max = (int) $equipe->qsession->quiz->score;
        if ($max > 0 && $value > $max) {
            $value = $max;
        }

        $equipe->qsession->score = $value;
        $equipe->qsession->state = 1;
        $equipe->qsession->save();
    }

    public  function updatingStatut()
    {
        if ($this->afficher == '') {
            $this->afficher = true;
        } else {
            $this->afficher = '';
        }
    }



    public function selection(int $id)
    {
        if ($id) {
            $equipe = Equipe::where('id', $id)->first();
            $equipe->update([
                'statut' => !$equipe->statut
            ]);
        }
    }

    public function autoSelct()
    {
        $quiz = Quiz::where('niveau_id', $this->niveauselect)->first();

        $qsW = Qsession::where('quiz_id', $quiz->id)->orderBy('score', 'desc')->get();
        $qS = Qsession::where('quiz_id', $quiz->id)->get();


        if ($this->nb_team >= sizeof($qsW)) {
            foreach ($qsW as $qs) {
                $e = $qs->equipe;
                $e->statut = 1;
                $e->save();
            }
        } else {
            for ($i = 0; $i < $this->nb_team; $i++) {
                $e = $qsW[$i]->equipe;
                $e->statut = 1;
                $e->save();
            }
        }

        foreach ($qS as $qs) {
            $e = $qs->equipe;
            if ($e->statut == 0) {
                $qs->score = $qs->score > 0 ? -$qs->score : $qs->score;
                $qs->save();
            }
        }
    }

    public function resetSelection()
    {
        $quiz = Quiz::where('niveau_id', $this->niveauselect)->first();
        $qsessions = Qsession::where('quiz_id', $quiz->id)->get();

        foreach ($qsessions as $qs) {
            $e = $qs->equipe;
            $e->statut = 0;
            $e->save();

            $qs->score = $qs->score < 0 ? -$qs->score : $qs->score;
            $qs->save();
        }
    }
}
