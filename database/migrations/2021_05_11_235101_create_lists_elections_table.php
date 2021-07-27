<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateListsElectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lists_elections', function (Blueprint $table) {
            $table->id();
            $table->string('name',255);
            $table->string('picture',255)->nullable();
            $table->string('program',400)->nullable();
           $table->integer("count")->default(0);
            $table->integer("seats_number")->default(1);
            $table->bigInteger('election_id')->unsigned();
            $table->foreign("election_id")->references('id')->on("ballots");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lists_elections');
    }
}
