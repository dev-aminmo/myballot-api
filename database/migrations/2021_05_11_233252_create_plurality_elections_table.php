<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePluralityElectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plurality_elections', function (Blueprint $table) {

            $table->bigInteger('id')->unsigned();
            $table->integer("seats_number")->default(1);

            $table->foreign("id")->references('id')->on("ballots")->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('plurality_elections');
    }
}
