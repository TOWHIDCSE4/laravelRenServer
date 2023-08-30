<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSupporterManageTowersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('supporter_manage_towers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('host_id')->unsigned()->index();
            $table->foreign('host_id')->references('id')->on('users')->onDelete('cascade');

            $table->unsignedBigInteger('supporter_id')->unsigned()->index();
            $table->foreign('supporter_id')->references('id')->on('users')->onDelete('cascade');

            $table->string("name")->nullable();
            $table->string("phone_number")->nullable();
            $table->string("email")->nullable();

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
        Schema::dropIfExists('supporter_manage_towers');
    }
}
