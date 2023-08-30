<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistoryPotentialUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('history_potential_users', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_guest_id')->unsigned()->index();
            $table->foreign('user_guest_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('user_host_id')->unsigned()->index();
            $table->foreign('user_host_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('title')->nullable();
            $table->integer('type_from')->nullable();
            $table->bigInteger('value_reference')->nullable();

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
        Schema::dropIfExists('history_potential_users');
    }
}
