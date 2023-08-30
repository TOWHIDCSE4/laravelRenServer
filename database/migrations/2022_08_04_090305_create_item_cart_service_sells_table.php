<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemCartServiceSellsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('item_cart_service_sells')) {
            Schema::create('item_cart_service_sells', function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('user_id')->unsigned()->index();
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->unsignedBigInteger('service_sell_id')->unsigned()->index();
                $table->foreign('service_sell_id')->references('id')->on('service_sells')->onDelete('cascade');
                $table->integer('quantity')->default(1)->nullable(); // số phòng
                $table->double("item_price")->default('0')->nullable();
                $table->longText('images')->nullable();

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
        Schema::dropIfExists('item_cart_service_sells');
    }
}
