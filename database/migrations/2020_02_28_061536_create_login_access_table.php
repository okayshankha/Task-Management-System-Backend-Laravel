<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoginAccessTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('login_access', function (Blueprint $table) {
            $table->bigIncrements('login_access_id');
            $table->string('username');
            $table->string('password');
            $table->string('role_id');
            $table->string('status')->default(config('GlobalValues.employeePending'));
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
        Schema::dropIfExists('login_access');
    }
}
