<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConnectManageMotelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('connect_manage_motels', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('supporter_manage_tower_id')->unsigned()->index();
            $table->foreign('supporter_manage_tower_id')->references('id')->on('supporter_manage_towers')->onDelete('cascade');

            $table->unsignedBigInteger('tower_id')->unsigned()->index();
            $table->foreign('tower_id')->references('id')->on('towers')->onDelete('cascade');

            $table->unsignedBigInteger('motel_id')->unsigned()->index();
            $table->foreign('motel_id')->references('id')->on('motels')->onDelete('cascade');

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
        Schema::dropIfExists('connect_manage_motels');
    }
}
