<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLotpacksHistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lotpacks_history', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('lote_id');
            $table->integer('parent_id');
            $table->date('fecha')->default('1980-10-02');
            $table->string('lote', 200)->nullable();
            $table->integer('num_start')->nullable();
            $table->integer('num_end')->nullable();
            $table->integer('fardos')->default(0)->nullable();
            $table->integer('b')->nullable();
            $table->integer('b14')->nullable();
            $table->integer('b12')->nullable();
            $table->integer('b34')->nullable();
            $table->integer('c')->nullable();
            $table->integer('c14')->nullable();
            $table->integer('c12')->nullable();
            $table->integer('c34')->nullable();
            $table->integer('d')->nullable();
            $table->integer('d14')->nullable();
            $table->integer('d12')->nullable();
            $table->integer('d34')->nullable();
            $table->integer('e')->nullable();
            $table->integer('e14')->nullable();
            $table->integer('e12')->nullable();
            $table->integer('e34')->nullable();
            $table->integer('f')->nullable();
            $table->integer('f14')->nullable();
            $table->integer('f12')->nullable();
            $table->integer('f34')->nullable();
            $table->string('micro', 200)->nullable();
            $table->string('fibra', 200)->nullable();
            $table->integer('color_id')->nullable();
            $table->text('notas')->nullable();
            $table->integer('vendedor_id');
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
        Schema::dropIfExists('lotpacks_history');
    }
}
