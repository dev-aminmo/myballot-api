<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartisanElectionListsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('partisan_election_lists', function (Blueprint $table) {
            $table->id();
            $table->string('name',255);
            $table->string('picture',255)->nullable();
            $table->string('program',400)->nullable();
            $table->integer("count")->default(0);
            $table->bigInteger('election_id')->unsigned();
            $table->foreign("election_id")->references('id')->on("lists_elections")->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('partisan_election_lists');
    }
}
