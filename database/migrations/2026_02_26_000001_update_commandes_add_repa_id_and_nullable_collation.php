<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateCommandesAddRepaIdAndNullableCollation extends Migration
{
    public function up()
    {
        Schema::table('commandes', function (Blueprint $table) {
            $table->unsignedBigInteger('repa_id')->nullable()->after('salle_id');
            $table->foreign('repa_id')->references('id')->on('repas')->onDelete('cascade');
        });

        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE commandes MODIFY collation_id BIGINT UNSIGNED NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE commandes ALTER COLUMN collation_id DROP NOT NULL');
        }
    }

    public function down()
    {
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE commandes MODIFY collation_id BIGINT UNSIGNED NOT NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE commandes ALTER COLUMN collation_id SET NOT NULL');
        }

        Schema::table('commandes', function (Blueprint $table) {
            $table->dropForeign(['repa_id']);
            $table->dropColumn('repa_id');
        });
    }
}
