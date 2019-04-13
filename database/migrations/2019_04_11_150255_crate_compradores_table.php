<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CrateCompradoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('compradores', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nombre', 250);
            $table->string('domicilio', 250);
            $table->integer('localidad_id');
            $table->integer('provincia_id');
            $table->string('codigo_postal', 250)->nullable();
            $table->string('iva', 250)->nullable();
            $table->string('cuit', 250)->nullable();
            $table->string('email', 250)->nullable();
            $table->string('telefono', 250)->nullable();
            $table->string('fax', 250)->nullable();
            $table->string('contacto', 250)->nullable();
            $table->string('horario', 250)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('compradores');
    }
}
