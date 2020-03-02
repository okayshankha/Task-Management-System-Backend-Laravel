<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->string('project_id')->primary();
            $table->string('name');
            $table->string('description');
            $table->string('estimated_hours');
            $table->string('created_by_login_access_id');
            $table->string('modified_by_login_access_id')->nullable();
            $table->string('manager_login_access_id')->nullable();
            $table->string('status')->default(config('GlobalValues.projectBacklog'));
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
        Schema::dropIfExists('projects');
    }
}
