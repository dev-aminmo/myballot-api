<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateListsCandidatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lists_candidates', function (Blueprint $table) {
            $table->bigInteger('id')->unsigned();
            $table->foreign("id")->references('id')->on("candidates")->onDelete('cascade');

            $table->bigInteger('list_id')->unsigned();
           $table->foreign("list_id")->references('id')->on("election_lists")->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lists_candidates');
    }
}
