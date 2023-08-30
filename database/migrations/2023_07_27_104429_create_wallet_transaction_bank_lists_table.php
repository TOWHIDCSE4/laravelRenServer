<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWalletTransactionBankListsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wallet_transaction_bank_lists', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            //BANK INFORMATION
            $table->string('en_name')->nullable();
            $table->string('vn_name')->nullable();
            $table->string('shortName')->nullable();
            $table->integer('bankId')->nullable();
            $table->string('bank_icon')->nullable();
            $table->integer('atmBin')->nullable();
            $table->integer('cardLength')->nullable();
            $table->integer('bankCode')->nullable();
            $table->tinyInteger('type');
            $table->tinyInteger('napasSupported');
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
        Schema::dropIfExists('wallet_transaction_bank_lists');
    }
}
