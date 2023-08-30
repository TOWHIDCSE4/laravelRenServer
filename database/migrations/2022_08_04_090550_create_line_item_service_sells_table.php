<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLineItemServiceSellsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('line_item_service_sells')) {
            Schema::create('line_item_service_sells', function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('user_id')->unsigned()->index();
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->unsignedBigInteger('order_service_sell_id')->unsigned()->index();
                $table->foreign('order_service_sell_id')->references('id')->on('order_service_sells')->onDelete('cascade');
                $table->integer('quantity')->default(1)->nullable(); // số phòng
                $table->double("item_price")->default(0)->nullable();
                $table->longText('images')->nullable();
                $table->bigInteger('service_sell_id');
                $table->double('total_price')->default(0)->nullable();
                $table->string('name_service_sell')->nullable();

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
        Schema::dropIfExists('line_item_service_sells');
    }
}
