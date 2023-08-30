<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostCategoryHelpPostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('post_category_help_posts')) {
            Schema::create('post_category_help_posts', function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('help_post_id')->unsigned()->index();
                $table->foreign('help_post_id')->references('id')->on('help_posts')->onDelete('cascade');

                $table->unsignedBigInteger('category_help_post_id')->unsigned()->index();
                $table->foreign('category_help_post_id')->references('id')->on('category_help_posts')->onDelete('cascade');

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
        Schema::dropIfExists('post_category_help_posts');
    }
}
