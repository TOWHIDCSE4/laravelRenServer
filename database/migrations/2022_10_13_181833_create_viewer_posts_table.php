<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateViewerPostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('viewer_posts')) {
            Schema::create('viewer_posts', function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('mo_post_id')->unsigned()->index();
                $table->foreign('mo_post_id')->references('id')->on('mo_posts')->cascadeOnDelete();
                $table->unsignedBigInteger('user_id')->unsigned()->index();

                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('viewer_posts');
    }
}
