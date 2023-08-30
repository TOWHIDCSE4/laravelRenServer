<?php

use App\Helper\Helper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateOrderServiceSellsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('order_service_sells')) {
            Schema::create('order_service_sells', function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('user_id')->unsigned()->index();
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->string("order_code")->unique();
                $table->integer("order_status")->default(0)->nullable();
                $table->integer("payment_status")->default(0)->nullable();
                $table->double("total_shipping_fee")->default(0)->nullable();
                $table->double("total_final")->default(0)->nullable();
                $table->string("name")->nullable();
                $table->string("phone_number")->nullable();
                $table->string("province_name")->nullable();
                $table->string("district_name")->nullable();
                $table->string("wards_name")->nullable();
                $table->integer("province")->nullable();
                $table->integer("district")->nullable();
                $table->integer("wards")->nullable();
                $table->string("address_detail")->nullable();
                $table->string("note")->nullable();
                $table->string('email')->nullable();
                $table->double('total_before_discount')->nullable()->default(0);
                $table->timestamp('date_payment')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));

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
        Schema::dropIfExists('order_service_sells');
    }
}
