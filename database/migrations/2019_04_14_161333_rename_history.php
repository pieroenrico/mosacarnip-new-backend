<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameHistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('lotes_history', 'history_lotes');
        Schema::rename('numeraciones_history', 'history_numeraciones');
        Schema::rename('lotpacks_history', 'history_lotpack');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::rename('history_lotes', 'lotes_history');
        Schema::rename('history_numeraciones', 'numeraciones_history');
        Schema::rename('history_lotpack', 'lotpacks_history');
    }
}
