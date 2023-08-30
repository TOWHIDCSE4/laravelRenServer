<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContractsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('contracts')) {
            Schema::create('contracts', function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('user_id')->unsigned()->index();
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->unsignedBigInteger('motel_id')->unsigned()->index();
                $table->foreign('motel_id')->references('id')->on('motels')->onDelete('cascade');
                $table->bigInteger('tower_id')->nullable();
                $table->timestamp('rent_from')->nullable();
                $table->timestamp('rent_to')->nullable();
                $table->integer('payment_space')->default(1)->nullable(); //Kỳ thanh toán 1, 2 ... tháng
                $table->double('money')->default(1)->nullable(); //Tiền phòng
                $table->double('deposit_money')->default(1)->nullable(); //Tiền đặt cọc
                $table->string('cmnd_number')->nullable();
                $table->string('cmnd_front_image_url')->nullable();
                $table->string('cmnd_back_image_url')->nullable();
                $table->integer('status')->default(2)->nullable(); //2 Đang đc thuê, 0 đã thanh lý
                $table->timestamp('pay_start')->nullable();

                $table->longText('images')->nullable();
                $table->longText('mo_services')->nullable();
                $table->string('note')->nullable();
                $table->double('deposit_amount_paid')->nullable()->default(0);
                $table->longText('images_deposit')->nullable();
                $table->longText('furniture')->nullable();
                $table->timestamp('deposit_payment_date')->nullable();
                $table->timestamp('deposit_used_date')->nullable();
                $table->double('deposit_actual_paid')->nullable();
                // $table->string('cmnd_number')->nullable();
                // $table->string('cmnd_front_image_url')->nullable();
                // $table->string('cmnd_back_image_url')->nullable();

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
        Schema::dropIfExists('contracts');
    }
}
