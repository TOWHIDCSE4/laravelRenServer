<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminDiscoverUisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('admin_discover_uis')) {
            Schema::create('admin_discover_uis', function (Blueprint $table) {
                $table->id();

                $table->integer('province')->nullable();
                $table->string('province_name')->nullable();
                $table->string('image')->nullable();
                $table->string('content')->nullable();

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
        Schema::dropIfExists('admin_discover_uis');
    }
}
