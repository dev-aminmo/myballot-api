<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartisanCandidatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('partisan_candidates', function (Blueprint $table) {
            $table->bigInteger('id')->unsigned();
            $table->foreign("id")->references('id')->on("candidates")->onDelete('cascade');

            $table->bigInteger('party_id')->unsigned();
           $table->foreign("party_id")->references('id')->on("parties")->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('partisan_candidates');
    }
}
