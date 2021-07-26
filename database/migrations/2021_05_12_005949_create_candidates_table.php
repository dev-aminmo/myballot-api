<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCandidatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('candidates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description',400)->nullable();
            $table->string('picture',255)->nullable();
            $table->integer('count')->default(0);
           // $table->enum('election_type',["plurality","lists"])->default("plurality");
            $table->bigInteger('type')->unsigned()->nullable();
            $table->foreign("type")->references('id')->on("candidate_types")->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('candidates');
    }
}
