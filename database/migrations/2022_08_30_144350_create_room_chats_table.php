<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoomChatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('room_chats')) {
            Schema::create('room_chats', function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('user_id')->unsigned()->index();
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
                $table->unsignedBigInteger('message_id')->unsigned()->index();
                $table->foreign('message_id')->references('id')->on('messages')->cascadeOnDelete();
                $table->integer('user_unread')->default(0)->nullable();

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
        Schema::dropIfExists('room_chats');
    }
}
