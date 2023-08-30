<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOtpCodePhonesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('otp_code_phones')) {
            Schema::create('otp_code_phones', function (Blueprint $table) {
                $table->id();

                $table->string('area_code')->nullable();
                $table->string('otp')->nullable();
                $table->string('phone')->nullable();
                $table->timestamp('time_generate')->nullable();
                $table->longText('content')->nullable();

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
        Schema::dropIfExists('otp_code_phones');
    }
}
