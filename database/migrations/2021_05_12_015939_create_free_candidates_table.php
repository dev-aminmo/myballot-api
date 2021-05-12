<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFreeCandidatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('free_candidates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description',400)->nullable();
            $table->string('picture',255)->nullable();
            $table->bigInteger('election_id')->unsigned()->nullable();
            $table->foreign("election_id")->references('id')->on("plurality_elections")->onDelete('cascade');
              $table->bigInteger('list_id')->unsigned()->nullable();
            $table->foreign("list_id")->references('id')->on("free_election_lists")->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('free_candidates');
    }
}
