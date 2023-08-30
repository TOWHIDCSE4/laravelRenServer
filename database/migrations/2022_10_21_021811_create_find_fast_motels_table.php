<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFindFastMotelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('find_fast_motels')) {
            Schema::create('find_fast_motels', function (Blueprint $table) {
                $table->id();

                $table->string('name')->nullable();
                $table->string('note')->nullable(); // tiêu đề
                $table->string("province_name")->nullable();
                $table->string("district_name")->nullable();
                $table->string("wards_name")->nullable();
                $table->integer("province")->nullable();
                $table->integer("district")->nullable();
                $table->integer("wards")->nullable();
                $table->string("address_detail")->nullable();
                $table->string('phone_number')->nullable(); // số người liên hệ cho thuê
                $table->integer("status")->nullable()->default(0);
                $table->double('price')->nullable()->default(0);
                $table->integer('capacity')->nullable()->default(0);

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
        Schema::dropIfExists('find_fast_motels');
    }
}
