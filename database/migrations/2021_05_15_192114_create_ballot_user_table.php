<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBallotUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ballot_user', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('ballot_id')->unsigned()->nullable();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->boolean('voted')->default(false);
            $table->foreign("ballot_id")->references('id')->on("ballots")->onDelete('cascade');
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
        Schema::dropIfExists('ballot_user');
    }
}
