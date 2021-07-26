<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCandidateTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('candidate_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });
        DB::table('candidate_types')->insert([
            'name'=>"plurality",
        ]);
        DB::table('candidate_types')->insert([
            'name'=>"lists",
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('candidate_types');
    }
}
