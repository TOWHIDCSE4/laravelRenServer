<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserContractsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('user_contracts')) {
            Schema::create('user_contracts', function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('user_id')->unsigned()->index();
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->unsignedBigInteger('motel_id')->unsigned()->index();
                $table->foreign('motel_id')->references('id')->on('motels')->onDelete('cascade');
                $table->unsignedBigInteger('contract_id')->unsigned()->index();
                $table->foreign('contract_id')->references('id')->on('contracts')->onDelete('cascade');
                $table->string('renter_phone_number');
                $table->tinyInteger('is_represent')->default(0)->nullable();

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
        Schema::dropIfExists('user_contracts');
    }
}
