<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVirtualAccountTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('virtual_account_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('result')->default(0);
            $table->string('checksum')->nullable();
            $table->integer('status')->nullable();
            $table->integer('error_code')->nullable();
            // $table->number('payment_no')->nullable();
            // $table->string('failure_reason')->default(0);
            // $table->string('message')->nullable();
            $table->integer('amount')->nullable();
            $table->string('method')->nullable();
            $table->integer('is_va')->nullable();
            $table->string('bank_code')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_va_name')->nullable();
            $table->string('bank_va_no')->nullable();
            $table->string('remark')->nullable();

            $table->string('account_no')->nullable();
            $table->string('description')->nullable();
            $table->string('account_name')->nullable();
            $table->string('account_type')->nullable();
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
        Schema::dropIfExists('virtual_account_transactions');
    }
}
