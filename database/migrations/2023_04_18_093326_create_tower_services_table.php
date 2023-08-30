<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTowerServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tower_services', function (Blueprint $table) {
            $table->id();


            $table->unsignedBigInteger('tower_id')->unsigned()->index();
            $table->foreign('tower_id')->references('id')->on('towers')->onDelete('cascade');
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

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tower_services');
    }
}
