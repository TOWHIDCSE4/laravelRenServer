<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoryHelpPostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('category_help_posts')) {
            Schema::create('category_help_posts', function (Blueprint $table) {
                $table->id();

                $table->boolean("is_show")->nullable();
                $table->string("image_url")->nullable();
                $table->string("title")->nullable();
                $table->string("description")->nullable();

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
        Schema::dropIfExists('category_help_posts');
    }
}
