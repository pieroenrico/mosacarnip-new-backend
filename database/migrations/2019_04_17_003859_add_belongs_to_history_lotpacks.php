<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBelongsToHistoryLotpacks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('history_lotpacks', function (Blueprint $table) {
            $table->integer('belongs_to')->after('parent_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('history_lotpacks', function (Blueprint $table) {
            $table->dropColumn('belongs_to');
        });
    }
}
