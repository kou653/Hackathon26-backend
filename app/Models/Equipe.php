<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Classe;
class Equipe extends Model
{
    use HasFactory;

    public $guarded = [] ;

    protected $appends = ['is_extern'];

    public function participants()
    {
    	return $this->hasMany(Participant::class);
    }

    public function niveau()
    {
    	return $this->belongsTo(Niveau::class);
    }

    public function qsession(){
        return $this->hasOne(Qsession::class);
    }

    public function currentSalle()
    {

        $hackaton = Hackaton::where('inscription', 1)->first();

        $salle = DB::table('rep_salles')
                    ->join('salles', 'salles.id', '=', 'rep_salles.salle_id')
                    ->where('equipe_id', $this->id)
                    ->where('hackaton_id', $hackaton->id)
                    ->select('*')
                    ->first();

        
        return $salle; 
    }
    public function getIsExternAttribute()
{
        $participant = $this->participants->first();

        // Aucun participant ou étudiant → externe
        if (!$participant || !$participant->etudiant) {
            return true;
        }

        // Recherche de la classe
        $classe = Classe::where(
            'libelle',
            $participant->etudiant->classe
        )->first();

        // Classe inexistante ou non ESATIC → externe
        return !$classe || $classe->esatic == 0;
    }
}
