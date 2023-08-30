<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUToUMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('u_to_u_messages')) {
            Schema::create('u_to_u_messages', function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('user_id')->unsigned()->index();
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
                $table->unsignedBigInteger('vs_user_id')->unsigned()->index();
                $table->foreign('vs_user_id')->references('id')->on('users')->cascadeOnDelete();
                $table->boolean('is_sender')->default(0)->nullable();
                $table->longText('content')->nullable();
                $table->longText('images')->nullable();
                $table->string('list_motel_id')->nullable();

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
        Schema::dropIfExists('u_to_u_messages');
    }
}
