<?php

use Illuminate\Database\Seeder;

class login_access_seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('login_access')->insert([
            'username' => 'admin',
            'password' => Hash::make('12345'),
            'role_id' => 1,
            'status' => 'active'
        ]);

        DB::table('login_access')->insert([
            'username' => 'employee',
            'password' => Hash::make('12345'),
            'role_id' => 2,
            'status' => 'active'
        ]);
    }
}
