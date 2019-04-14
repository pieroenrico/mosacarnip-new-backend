<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNumeracionesHistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('numeraciones_history', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('lotpack_id');
            $table->integer('sort_order');
            $table->string('calidad');
            $table->string('type');
            $table->integer('fardos')->default(0);
            $table->integer('num_start')->nullable();
            $table->integer('num_end')->nullable();
            $table->string('numeracion')->nullable();
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
        Schema::dropIfExists('numeraciones_history');
    }
}
