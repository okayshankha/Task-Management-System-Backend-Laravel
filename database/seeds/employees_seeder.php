<?php

use Illuminate\Database\Seeder;

class employees_seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('employees')->insert([
            'employee_id' => "EMP_4854358485Q",
            'login_access_id' => 1,
            'fname' => 'Shankhadeep',
            'lname' => 'Das',
            'email' => 'vulval@gmail.com',
            'mobile' => '1234567890',
            'address' => 'Kolkata, Salt Lake, Sec V, 700071',
            'created_by_access_id' => 'system',
            'status' => config('GlobalValues.employeeValid')
        ]);

        DB::table('employees')->insert([
            'employee_id' => "EMP_485435885Z",
            'login_access_id' => 2,
            'fname' => 'Employee',
            'lname' => 'Name',
            'email' => 'vulval2@gmail.com',
            'mobile' => '1234567890',
            'address' => 'Kolkata, Salt Lake, Sec V, 700071',
            'created_by_access_id' => '1',
            'status' => config('GlobalValues.employeeValid')
        ]);
    }
}
