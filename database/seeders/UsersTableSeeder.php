<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
      $user=  User::create([
      'name'=>"most",
      'email'=>"most@gmail.com",
      'password'=>bcrypt('123456'),]
        );
      $user->attachRole('organizer');
      /*$user=  User::create([
      'name'=>"mo amin",
      'email'=>"momo@gmail.com",
      'password'=>bcrypt('123456'),]
        );
      $user->attachRole('organizer');
      $user=  User::create([
      'name'=>"molacha",
      'email'=>"movoter@gmail.com",
      'password'=>bcrypt('123456'),]
        );
      $user->attachRole('voter')*/;
    }
}
