<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLastSeenMoPostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('last_seen_mo_posts')) {
            Schema::create('last_seen_mo_posts', function (Blueprint $table) {
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
        Schema::dropIfExists('last_seen_mo_posts');
    }
}
