<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(login_access_seeder::class);
        $this->call(employees_seeder::class);
        $this->call(roles_seeder::class);
    }
}
