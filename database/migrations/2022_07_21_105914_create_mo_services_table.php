<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMoServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('mo_services')) {
            Schema::create('mo_services', function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('motel_id')->unsigned()->index();
                $table->foreign('motel_id')->references('id')->on('motels')->onDelete('cascade');
                $table->string('service_name')->nullable(); //  điện ,water nước,  wifi , giặt, gửi xe
                $table->string('service_icon')->nullable(); //  điện ,water nước,  wifi , giặt, gửi xe
                $table->string('service_unit')->default(0)->nullable(); //Đơn vị (Kwn, m3, phòng, người, xe, lần) //đơn vị tính
                $table->double('service_charge')->default(0)->nullable(); //Phí dịch vụ nhân theo số hệ số đơn vị dịch vụ dịch vụ
                $table->longText('note')->nullable(); // Mô tả

                $table->integer('type_unit')->nullable()->default(0);
                $table->longText('images')->nullable();

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
        Schema::dropIfExists('mo_services');
    }
}
