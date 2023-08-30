<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReservationMotelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('reservation_motels')) {
            Schema::create('reservation_motels', function (Blueprint $table) {
                $table->id();

                $table->string('name')->nullable();
                $table->string('note')->nullable();
                $table->string('province_name')->nullable();
                $table->string('wards_name')->nullable();
                $table->integer('province')->nullable();
                $table->integer('district')->nullable();
                $table->integer('wards')->nullable();
                $table->integer('status')->nullable();
                $table->string('address_detail')->nullable();
                $table->string('phone_number')->nullable();
                $table->string('district_name')->nullable();
                $table->bigInteger('user_id')->nullable();
                $table->bigInteger('mo_post_id')->nullable();
                $table->bigInteger('host_id')->nullable();

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
        Schema::dropIfExists('reservation_motels');
    }
}
