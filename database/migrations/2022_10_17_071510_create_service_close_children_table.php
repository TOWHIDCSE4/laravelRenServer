<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceCloseChildrenTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('service_close_children')) {
            Schema::create('service_close_children', function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('service_close_id')->unsigned()->index();
                $table->foreign('service_close_id')->references('id')->on('service_closes')->onDelete('cascade');

                $table->string('service_name')->default(0)->nullable(); //  điện ,water nước,  wifi , giặt, gửi xe
                $table->string('service_icon')->default(0)->nullable(); //  điện ,water nước,  wifi , giặt, gửi xe
                $table->string('service_unit')->default(0)->nullable(); //Đơn vị (Kwn, m3, phòng, người, xe, lần) //đơn vị tính
                $table->double('service_charge')->default(0)->nullable(); //Phí dịch vụ nhân theo số hệ số đơn vị dịch vụ dịch vụ
                $table->longText('images')->nullable();
                $table->integer('type_unit')->nullable()->default(0);
                $table->integer('quantity')->nullable()->default(0);
                $table->double('total')->default(0)->nullable(); //Phí dịch vụ nhân theo số hệ số đơn vị dịch vụ dịch vụ
                $table->double('old_quantity')->default(0)->nullable();

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
        Schema::dropIfExists('service_close_children');
    }
}
