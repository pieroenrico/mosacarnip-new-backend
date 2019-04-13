<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class NullablesPacks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('packs', function (Blueprint $table) {
            $table->string('micro', 200)->nullable()->change();
            $table->string('fibra', 200)->nullable()->change();
            $table->text('notas')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('packs', function (Blueprint $table) {
            $table->string('micro', 200)->nullable(false)->change();
            $table->string('fibra', 200)->nullable(false)->change();
            $table->text('notas')->nullable(false)->change();
        });
    }
}
