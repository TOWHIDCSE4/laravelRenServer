<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMoPostMultipleMotelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mo_post_multiple_motels', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('motel_id')->unsigned()->index();
            $table->foreign('motel_id')->references('id')->on('motels')->onDelete('cascade');
            $table->unsignedBigInteger('mo_post_id')->unsigned()->index();
            $table->foreign('mo_post_id')->references('id')->on('mo_posts')->onDelete('cascade');

            $table->string('motel_name')->nullable();
            $table->integer('floor')->nullable()->default(0);
            $table->integer('area')->nullable()->default(0);
            $table->double('money')->nullable()->default(0);

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
        Schema::dropIfExists('mo_post_multiple_motels');
    }
}
