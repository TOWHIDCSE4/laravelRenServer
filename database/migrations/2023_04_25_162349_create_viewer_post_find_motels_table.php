<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateViewerPostFindMotelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('viewer_post_find_motels', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('mo_post_find_motel_id')->unsigned()->index();
            $table->foreign('mo_post_find_motel_id')->references('id')->on('mo_post_find_motels')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id')->unsigned()->index();

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
        Schema::dropIfExists('viewer_post_find_motels');
    }
}
