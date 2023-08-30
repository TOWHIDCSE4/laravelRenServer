<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTowerMotelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tower_motels', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('tower_id')->unsigned()->index();
            $table->foreign('tower_id')->references('id')->on('towers')->onDelete('cascade');
            $table->unsignedBigInteger('motel_id')->unsigned()->index();
            $table->foreign('motel_id')->references('id')->on('motels')->onDelete('cascade');
            $table->integer('status')->nullable();
            $table->integer('is_room')->nullable();

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
        Schema::dropIfExists('tower_motels');
    }
}
