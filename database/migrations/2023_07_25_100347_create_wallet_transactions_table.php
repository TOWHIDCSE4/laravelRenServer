<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWalletTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            //BANK INFORMATION
            $table->string('account_number')->nullable();
            $table->string('bank_account_holder_name')->nullable();
            $table->string('bank_name')->nullable();
            $table->float('rest_money')->default(0);
            $table->integer('otp_code')->nullable();
            // Deposit 
            $table->float('deposit_money')->default(0);
            $table->string('deposit_trading_code')->nullable();
            $table->timestamp('deposit_date_time')->nullable();
            $table->string('deposit_content')->nullable();
            // withdraw 
            $table->float('withdraw_money')->default(0);
            $table->string('withdraw_trading_code')->nullable();
            $table->timestamp('withdraw_date_time')->nullable();
            $table->string('withdraw_content')->nullable();
            // admin review by status
            $table->float('bonus')->default(0);
            $table->tinyInteger('type');
            $table->tinyInteger('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wallet_transactions');
    }
}
