<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $admin=  Role::create([
                'name'=>"admin",
                'display_name'=>"admin",
                'description'=>"can manage elections,polls",]
        );
        $user=  Role::create([
                'name'=>"voter",
                'display_name'=>"voter",
                'description'=>"can vote",]
        );
    }
}
