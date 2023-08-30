<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceSellsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('service_sells')) {
            Schema::create('service_sells', function (Blueprint $table) {
                $table->id();

                $table->string("name")->nullable();
                $table->string("name_str_filter")->nullable();
                $table->longText("images")->nullable();
                $table->double("price")->default(0)->nullable();
                $table->integer("sold")->default(0)->nullable();
                $table->integer("status")->default(2)->nullable(); // 0 ẩn 2 hiện
                $table->string('seller_service_name')->nullable();
                $table->string('phone_number_seller_service')->nullable();
                $table->string('service_sell_icon')->nullable();
                $table->string('description')->nullable();

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
        Schema::dropIfExists('service_sells');
    }
}
