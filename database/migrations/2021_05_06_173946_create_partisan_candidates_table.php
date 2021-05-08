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
            $table->id();
            $table->string('name');
            $table->string('description',400)->nullable();
            $table->string('picture',255)->nullable();
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
