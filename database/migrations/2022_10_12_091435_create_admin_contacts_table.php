<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('admin_contacts')) {
            Schema::create('admin_contacts', function (Blueprint $table) {
                $table->id();

                $table->string('facebook')->nullable();
                $table->string('zalo')->nullable();
                $table->string('email')->nullable();
                $table->string('phone_number')->nullable();
                $table->string('hotline')->nullable();
                $table->string('bank_account_number')->nullable();
                $table->string('content')->nullable();
                $table->string("address")->nullable();

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
        Schema::dropIfExists('admin_contacts');
    }
}
