<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('services')) {
            Schema::create('services', function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('user_id')->unsigned()->index();
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

                $table->string('service_name')->default(0)->nullable(); //  điện ,water nước,  wifi , giặt, gửi xe
                $table->string('service_icon')->default(0)->nullable(); //  điện ,water nước,  wifi , giặt, gửi xe
                $table->string('service_unit')->default(0)->nullable(); //Đơn vị (Kwn, m3, phòng, người, xe, lần) //đơn vị tính
                $table->double('service_charge')->default(0)->nullable(); //Phí dịch vụ nhân theo số hệ số đơn vị dịch vụ dịch vụ
                $table->longText('note')->nullable(); // Mô tả
                $table->integer('type_unit')->nullable()->default(0);
                $table->tinyInteger('is_default')->nullable()->default(0);

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
        Schema::dropIfExists('services');
    }
}
