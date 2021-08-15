<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('question_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });
        DB::table('question_types')->insert([
            'name'=>"question with single choice",
        ]);
        DB::table('question_types')->insert([
            'name'=>"question with multiple choice",
        ]);

        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->string('value',400);
            $table->bigInteger('type_id')->unsigned();
            $table->bigInteger('poll_id')->unsigned();
            $table->foreign("type_id")->references('id')->on("question_types");
            $table->foreign("poll_id")->references('id')->on("ballots")->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('questions');
        Schema::dropIfExists('question_types');
    }
}
