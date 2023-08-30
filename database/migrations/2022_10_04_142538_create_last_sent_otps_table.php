<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLastSentOtpsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('last_sent_otps')) {
            Schema::create('last_sent_otps', function (Blueprint $table) {
                $table->id();
                $table->string('area_code')->nullable();
                $table->string('otp')->nullable();
                $table->string('phone')->nullable();
                $table->string('ip')->nullable();
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
        Schema::dropIfExists('last_sent_otps');
    }
}
