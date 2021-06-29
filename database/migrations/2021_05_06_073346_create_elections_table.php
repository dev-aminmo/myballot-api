<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateElectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('elections', function (Blueprint $table) {
            $table->id();
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->string('title',255);
            $table->string('description',400)->nullable();
            //$table->boolean('type')->default(0);
            $table->enum('type',["plurality","lists","poll"]);
            $table->boolean('candidate_type')->default(0);
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
        Schema::dropIfExists('elections');
    }
}
