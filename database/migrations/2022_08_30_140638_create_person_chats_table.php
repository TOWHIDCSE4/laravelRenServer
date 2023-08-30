<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePersonChatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('person_chats')) {
            Schema::create('person_chats', function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('user_id')->unsigned()->index();
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
                $table->unsignedBigInteger('to_user_id')->unsigned()->index();
                $table->foreign('to_user_id')->references('id')->on('users')->cascadeOnDelete();
                $table->longText('last_mess')->nullable();
                $table->boolean('is_my_last_message')->nullable();
                $table->boolean('seen')->default(false)->nullable();
                $table->boolean('is_helper')->default(false)->nullable();
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
        Schema::dropIfExists('person_chats');
    }
}
