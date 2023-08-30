<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHelpFindMotelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('help_find_motels')) {
            Schema::create('help_find_motels', function (Blueprint $table) {
                $table->id();

                $table->string('facebook')->nullable();
                $table->string('zalo')->nullable();
                $table->string('name')->nullable();
                $table->string('phone_number')->nullable();
                $table->string('content')->nullable();

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
        Schema::dropIfExists('help_find_motels');
    }
}
