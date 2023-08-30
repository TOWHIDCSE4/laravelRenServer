<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeFieldPriceInMoPostFindMotelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('mo_post_find_motels', 'money_from') && !Schema::hasColumn('mo_post_find_motels', 'money_to')) {
            Schema::table('mo_post_find_motels', function (Blueprint $table) {
                $table->double('money_from')->default(0)->nullable()->after('price'); // số tiền thuê vnd/ phòng bắt đầu
                $table->double('money_to')->default(0)->nullable()->after('money_from');; // số tiền thuê vnd/ phòng tới
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
        Schema::table('mo_post_find_motels', function (Blueprint $table) {
            //
        });
    }
}
