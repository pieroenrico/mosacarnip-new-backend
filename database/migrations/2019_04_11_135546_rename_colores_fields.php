<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameColoresFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('colores', function (Blueprint $table) {
            $table->renameColumn('name', 'nombre');
            $table->renameColumn('color_code', 'item_code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('colores', function (Blueprint $table) {
            $table->renameColumn('nombre', 'name');
            $table->renameColumn('item_code', 'color_code');
        });
    }
}
