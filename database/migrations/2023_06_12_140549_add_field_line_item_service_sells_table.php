<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldLineItemServiceSellsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('line_item_service_sells', function (Blueprint $table) {
            $table->unsignedBigInteger('category_service_sell_id')->unsigned()->index()->nullable()->after('id');
            $table->foreign('category_service_sell_id')->references('id')->on('category_service_sells')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('line_item_service_sells', function (Blueprint $table) {
            //
        });
    }
}
