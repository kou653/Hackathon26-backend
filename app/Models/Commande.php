<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commande extends Model
{
    use HasFactory;
    public $guarded = [] ;

    public function etudiant()
    {
        return $this->belongsTo(Etudiant::class);
    }

    public function salle()
    {
        return $this->belongsTo(Salle::class);
    }

    public function repa()
    {
        return $this->belongsTo(Repa::class);
    }

    public function collation()
    {
    	return $this->belongsTo(Collation::class);
    }
}
