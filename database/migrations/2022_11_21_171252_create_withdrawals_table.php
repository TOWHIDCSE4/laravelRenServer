<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWithdrawalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('withdrawals')) {
            Schema::create('withdrawals', function (Blueprint $table) {
                $table->id();

                $table->bigInteger('user_id')->nullable();
                $table->bigInteger('admin_id')->nullable();
                $table->double('amount_money')->nullable()->default(0);
                $table->integer('status')->nullable()->default(0);
                $table->string('note')->nullable();
                $table->timestamp('date_withdrawal_approved')->nullable();

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
        Schema::dropIfExists('withdrawals');
    }
}
