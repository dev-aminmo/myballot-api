<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBallotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ballots', function (Blueprint $table) {
            $table->id();
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->string('title',255);
            $table->string('description',400)->nullable();
            $table->bigInteger('type')->unsigned()->nullable();
            $table->foreign("type")->references('id')->on("ballot_types")->onDelete('cascade');
            $table->integer("seats_number")->default(1);

            $table->bigInteger('organizer_id')->unsigned();
            $table->foreign("organizer_id")->references('id')->on("users")->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ballots');
    }
}
