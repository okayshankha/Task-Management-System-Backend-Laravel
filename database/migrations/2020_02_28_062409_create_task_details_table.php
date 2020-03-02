<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaskDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('task_details', function (Blueprint $table) {
            $table->string('task_details_id')->primary();
            $table->string('parent_task_details_id')->nullable();
            $table->string('project_id');
            $table->string('name');
            $table->string('description');
            $table->string('estimated_hours');
            $table->string('actual_hours');
            $table->string('assigned_to_login_access_id')->nullable();
            $table->string('assigned_by_login_access_id')->nullable();
            $table->string('assignment_comment')->nullable();
            $table->string('assigned_at')->nullable();
            $table->string('created_by_login_access_id');
            $table->string('modified_by_login_access_id')->nullable();
            $table->string('status')->default(config('GlobalValues.taskValid'));
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
        Schema::dropIfExists('task_details');
    }
}
