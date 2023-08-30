<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePotentialUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('potential_users', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_guest_id')->unsigned()->index();
            $table->foreign('user_guest_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('user_host_id')->unsigned()->index();
            $table->foreign('user_host_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('title')->nullable();
            $table->integer('type_from')->nullable();
            $table->integer('status')->nullable()->default(0);
            $table->bigInteger('value_reference')->nullable();
            $table->timestamp('time_interact')->nullable();

            $table->boolean('is_renter')->nullable()->default(false);
            $table->boolean('is_has_contract')->nullable()->default(false);
            $table->string("name_tower")->nullable();
            $table->string("name_motel")->nullable();

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
        Schema::dropIfExists('potential_users');
    }
}
