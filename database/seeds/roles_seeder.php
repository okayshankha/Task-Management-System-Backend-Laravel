<?php

use Illuminate\Database\Seeder;

class roles_seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('roles')->insert([
            'name' => 'admin',
            'description' => 'Administrator',
            'created_by_login_access_id' => 1,
            'modified_by_login_access_id' => null,
            'status' => 'valid'
        ]);

        DB::table('roles')->insert([
            'name' => 'employee',
            'description' => 'Employee',
            'created_by_login_access_id' => 1,
            'modified_by_login_access_id' => null,
            'status' => 'valid'
        ]);
    }
}
