<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeesProjectMapTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employees_project_map', function (Blueprint $table) {
            $table->bigIncrements('employees_project_map_id');
            $table->string('project_id');
            $table->string('login_access_id');
            $table->string('created_by_login_access_id');
            $table->string('modified_by_login_access_id')->nullable();
            $table->string('status')->default(config('GlobalValues.employeesProjectMapValid'));
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employees_project_map');
    }
}
