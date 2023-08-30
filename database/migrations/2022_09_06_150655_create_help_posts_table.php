<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHelpPostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('help_posts')) {
            Schema::create('help_posts', function (Blueprint $table) {
                $table->id();

                $table->string('title')->nullable();
                $table->string('image_url')->nullable();
                $table->longText('summary')->nullable();
                $table->longText('content')->nullable();
                $table->boolean('is_show')->nullable();
                $table->integer('count_view')->default(0);;

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
        Schema::dropIfExists('help_posts');
    }
}
