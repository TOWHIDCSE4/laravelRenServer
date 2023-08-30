<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMoneyMinMaxTowerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('towers', function (Blueprint $table) {
            // $table->double('min_money')->nullable()->default(0)->after('money');
            // $table->double('max_money')->nullable()->default(0)->after('min_money');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('towers', function (Blueprint $table) {
            //
        });
    }
}
