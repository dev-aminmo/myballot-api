<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePluralityElectionUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plurality_election_user', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('plurality_election_id')->unsigned()->nullable();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->boolean('voted')->default(false);
            $table->foreign("plurality_election_id")->references('id')->on("plurality_elections")->onDelete('cascade');
            $table->foreign("user_id")->references('id')->on("users")->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('plurality_election_user');
    }
}
