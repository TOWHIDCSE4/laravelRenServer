<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRentersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('renters')) {
            Schema::create('renters', function (Blueprint $table) {
                $table->id();
                $table->bigInteger('user_id')->nullable();
                $table->bigInteger('motel_id')->nullable()->unsigned();
                $table->bigInteger('tower_id')->nullable()->unsigned();
                $table->string("name")->nullable();
                $table->string("phone_number")->nullable();
                $table->string("email")->nullable();
                $table->string("cmnd_number")->nullable();
                $table->string("cmnd_front_image_url")->nullable();
                $table->string("cmnd_back_image_url")->nullable();
                $table->string("address")->nullable();

                $table->string("image_url")->nullable();
                $table->string("name_tower_expected")->nullable();
                $table->string("name_motel_expected")->nullable();
                $table->double("price_expected")->nullable()->default(0);
                $table->double("deposit_expected")->nullable()->default(0);
                $table->timestamp("estimate_rental_period")->nullable();
                $table->timestamp("estimate_rental_date")->nullable();
                $table->boolean("has_contract")->nullable()->default(0);
                $table->boolean("is_hidden")->nullable()->default(0);


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
        Schema::dropIfExists('renters');
    }
}
