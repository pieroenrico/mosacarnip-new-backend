<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDespachosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('despachos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('remito_id');
            $table->integer('fardos')->nullable();
            $table->string('micro')->nullable();
            $table->string('fibra')->nullable();
            $table->string('kilos')->nullable();
            $table->string('cosecha')->nullable();
            $table->string('precio')->nullable();
            $table->string('puesto')->nullable();
            $table->string('entrega')->nullable();
            $table->string('embarque')->nullable();
            $table->string('seguro')->nullable();
            $table->string('pago')->nullable();
            $table->string('arbitraje')->nullable();
            $table->string('kilaje_promedio')->nullable();
            $table->text('observaciones')->nullable();
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
        Schema::dropIfExists('despachos');
    }
}
