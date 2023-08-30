<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistoryMotelBillOtpsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('history_motel_bill_otps')) {
            Schema::create('history_motel_bill_otps', function (Blueprint $table) {
                $table->id();

                $table->bigInteger('renter_id')->nullable();
                $table->bigInteger('user_id')->nullable();
                $table->bigInteger('contract_id')->nullable();
                $table->string('phone_number')->nullable();
                $table->longText('content')->nullable();
                $table->tinyInteger('is_send')->nullable()->default(0);

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
        Schema::dropIfExists('history_motel_bill_otps');
    }
}
