<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePluralityCandidatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plurality_candidates', function (Blueprint $table) {
            $table->bigInteger('id')->unsigned();
            $table->foreign("id")->references('id')->on("candidates")->onDelete('cascade');

            $table->bigInteger('election_id')->unsigned();
           $table->foreign("election_id")->references('id')->on("ballots")->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('plurality_candidates');
    }
}
