<?php

namespace App\Http\Livewire\Participants;

use App\Models\QsessionResponse;
use App\Models\Response as QuizResponse;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Quiz extends Component
{

    // public $count_down = 18;

    public $current_index = 0;

    public $next = true;

    public $sponses;

    public $score = 0;

    protected $listeners = [
        'storeAndMove',
        'storeAndExit',
        'openSession'
    ];

    public function render()
    {
        // $this->closeSession();
        return view('livewire.participants.quiz', [
            'quiz' => Auth::user()->etudiant->getEquipe()->qsession->quiz
        ]);
    }

    public function openSession()
    {
        $ss = Auth::user()->etudiant->getEquipe()->qsession;
        $ss->state = 1;
        $ss->save();
    }

    public function moveQuestion($n)
    {
        $qq = Auth::user()->etudiant->getEquipe()->qsession->quiz->questions;

        if($this->current_index + $n == sizeof($qq) -1){
            $this->next = false;
        }else{
            $this->next = true;
        }

        if ($this->current_index + $n == sizeof($qq)) {
            return redirect()->to('/preselection');
        }

        $this->current_index += $n;
    }

    public function storeAnswers()
    {
        $ss = Auth::user()->etudiant->getEquipe()->qsession;
        $st = Auth::user()->etudiant->getEquipe()->qsession->quiz->questions[$this->current_index];
        $responses = QsessionResponse::where('qsession_id', $ss->id)->where('question_id', $st->id)->get();

        // Backfill missing session-response rows for old sessions.
        if ($responses->isEmpty()) {
            $questionResponses = QuizResponse::where('question_id', $st->id)->get();
            foreach ($questionResponses as $questionResponse) {
                QsessionResponse::firstOrCreate(
                    [
                        'qsession_id' => $ss->id,
                        'response_id' => $questionResponse->id,
                        'question_id' => $st->id
                    ],
                    [
                        'score' => $questionResponse->score,
                        'state' => 0
                    ]
                );
            }
            $responses = QsessionResponse::where('qsession_id', $ss->id)->where('question_id', $st->id)->get();
        }

        $sc = 0;

        foreach ($responses as $response) {
            $isChecked = $this->sponses && array_key_exists($response->response_id, $this->sponses) && $this->sponses[$response->response_id] == true;
            $response->state = $isChecked ? 1 : 0;
            $response->save();
            if ($isChecked) {
                $sc += $response->score;
            }
        }

        if ($sc < 0)
            $sc = 0;

        $this->score += $sc;
        $ss->score = $this->score;
        $ss->save();
    }

    public function storeAndMove($n)
    {
        $this->storeAnswers();
        $this->moveQuestion($n);
    }

    public function storeAndExit()
    {
        $this->storeAnswers();
        return redirect()->to('/preselection');
    }

    public function see()
    {
        dd($this->sponses);
    }
}
