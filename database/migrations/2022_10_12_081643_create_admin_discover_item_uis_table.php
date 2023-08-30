<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminDiscoverItemUisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('admin_discover_item_uis')) {
            Schema::create('admin_discover_item_uis', function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('admin_discover_id')->unsigned()->index();
                $table->foreign('admin_discover_id')->references('id')->on('admin_discover_uis')->onDelete('cascade');
                $table->string('content')->nullable();
                $table->string('image')->nullable();
                $table->integer('district')->nullable();
                $table->string('district_name')->nullable();

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
        Schema::dropIfExists('admin_discover_item_uis');
    }
}
