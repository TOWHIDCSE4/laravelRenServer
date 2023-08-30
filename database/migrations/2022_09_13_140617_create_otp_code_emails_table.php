<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOtpCodeEmailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('otp_code_emails')) {
            Schema::create('otp_code_emails', function (Blueprint $table) {
                $table->id();

                $table->string('otp')->nullable()->default(null);
                $table->string('email')->nullable()->default(null);
                $table->timestamp('time_generate')->nullable()->default(null);
                $table->longText('content')->nullable()->default(null);

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
        Schema::dropIfExists('otp_code_emails');
    }
}
