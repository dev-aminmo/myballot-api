<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('parties', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('picture',255)->nullable();
            $table->bigInteger('list_id')->unsigned();
            $table->bigInteger('election_id')->unsigned();
            $table->foreign("list_id")->references('id')->on("partisan_election_lists")->onDelete('cascade');
            $table->foreign("election_id")->references('id')->on("plurality_elections")->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('parties');
    }
}
