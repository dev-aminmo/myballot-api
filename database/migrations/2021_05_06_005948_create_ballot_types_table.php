<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBallotTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ballot_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });
        DB::table('ballot_types')->insert([
            'name'=>"plurality election",
        ]);
        DB::table('ballot_types')->insert([
            'name'=>"lists election",
        ]);
        DB::table('ballot_types')->insert([
            'name'=>"poll",
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ballot_types');
    }
}
