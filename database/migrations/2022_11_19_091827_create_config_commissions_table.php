<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConfigCommissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('config_commissions')) {
            Schema::create('config_commissions', function (Blueprint $table) {
                $table->id();

                $table->bigInteger('user_host_id')->nullable();
                $table->bigInteger('user_admin_id')->nullable();
                $table->bigInteger('user_id')->nullable();
                $table->bigInteger('motel_id')->nullable();
                $table->integer('status_host')->nullable()->default(0);
                $table->double('money_commission_admin')->nullable()->default(0);
                $table->double('money_commission_user')->nullable()->default(0);
                $table->integer('status_admin')->nullable()->default(0);
                $table->string('note')->nullable();

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
        Schema::dropIfExists('config_commissions');
    }
}
