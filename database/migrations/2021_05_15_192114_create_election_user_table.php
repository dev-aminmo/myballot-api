<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateElectionUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('election_user', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('election_id')->unsigned()->nullable();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->boolean('voted')->default(false);
            $table->foreign("election_id")->references('id')->on("elections")->onDelete('cascade');
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
        Schema::dropIfExists('election_user');
    }
}
