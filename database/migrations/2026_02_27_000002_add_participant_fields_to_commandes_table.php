<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddParticipantFieldsToCommandesTable extends Migration
{
    public function up()
    {
        Schema::table('commandes', function (Blueprint $table) {
            $table->string('participant_nom')->nullable()->after('collation_id');
            $table->string('equipe_nom')->nullable()->after('participant_nom');
            $table->string('salle_nom')->nullable()->after('equipe_nom');
        });
    }

    public function down()
    {
        Schema::table('commandes', function (Blueprint $table) {
            $table->dropColumn(['participant_nom', 'equipe_nom', 'salle_nom']);
        });
    }
}
