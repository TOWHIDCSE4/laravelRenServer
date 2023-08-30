<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMoPostRoommatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mo_post_roommates', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id')->unsigned()->index();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('motel_id')->nullable(); // Id motel
            $table->string('phone_number')->nullable(); // số người liên hệ
            $table->string('title')->nullable(); // tiêu đề
            $table->longText('description')->nullable(); // nội dung mô tả
            $table->string('motel_name')->nullable(); // số phòng
            $table->integer('capacity')->default(1)->nullable(); // sức chứa người/phòng
            $table->integer('sex')->default(0)->nullable(); // giới tính 0 tất cả, 1 nam , 2 nữ
            $table->double('area')->default(0)->nullable(); // diện tích m2
            $table->double('money')->default(0)->nullable(); // số tiền thuê vnd/ phòng
            $table->double('deposit')->default(0)->nullable(); // đặt cọc
            $table->double('electric_money')->default(0)->nullable(); // tiền điện - 0 là free
            $table->double('water_money')->default(0)->nullable(); // tiền nước - 0 là free
            $table->boolean('has_wifi')->default(true)->nullable();
            $table->double('wifi_money')->default(0)->nullable(); // tiền wifi - 0 là free
            $table->boolean("has_park")->default(1)->nullable(); //có chỗ để xe không
            $table->double('park_money')->default(0)->nullable(); // phí đỗ xe
            $table->string("province_name")->nullable();
            $table->string("district_name")->nullable();
            $table->string("wards_name")->nullable();
            $table->integer("number_tenant_current")->nullable()->default(0);
            $table->integer("number_find_tenant")->nullable()->default(0);
            $table->integer("floor")->nullable();
            $table->integer("province")->nullable();
            $table->integer("district")->nullable();
            $table->integer("wards")->nullable();
            $table->string("address_detail")->nullable();
            $table->boolean('has_wc')->default(true)->nullable();
            $table->boolean('has_window')->default(false)->nullable();
            $table->boolean('has_security')->default(true)->nullable();
            $table->boolean('has_free_move')->default(false)->nullable(); //tự do
            $table->boolean('has_own_owner')->default(false)->nullable(); //chủ riêng
            $table->boolean('has_air_conditioner')->default(false)->nullable();
            $table->boolean('has_water_heater')->default(false)->nullable();
            $table->boolean('has_kitchen')->default(false)->nullable();
            $table->boolean('has_fridge')->default(false)->nullable(); //tủ lạnh
            $table->boolean('has_washing_machine')->default(false)->nullable(); //tủ lạnh
            $table->boolean('has_mezzanine')->default(false)->nullable(); //gác lửng
            $table->boolean('has_bed')->default(false)->nullable(); //giường
            $table->boolean('has_wardrobe')->default(false)->nullable(); //tủ
            $table->boolean('has_tivi')->default(false)->nullable(); //tủ
            $table->boolean('has_pet')->default(false)->nullable(); //thú cưng
            $table->boolean('has_balcony')->default(false)->nullable(); //thú cưng
            $table->integer("hour_open")->default(0)->nullable();
            $table->integer("minute_open")->default(0)->nullable();
            $table->integer("hour_close")->default(0)->nullable();
            $table->integer("minute_close")->default(0)->nullable();
            $table->tinyInteger('has_finger_print')->default(0)->nullable();
            $table->tinyInteger('has_kitchen_stuff')->default(0)->nullable();
            $table->tinyInteger('has_table')->default(0)->nullable();
            $table->tinyInteger('has_decorative_lights')->default(0)->nullable();
            $table->tinyInteger('has_picture')->default(0)->nullable();
            $table->tinyInteger('has_tree')->default(0)->nullable();
            $table->tinyInteger('has_pillow')->default(0)->nullable();
            $table->tinyInteger('has_mattress')->default(0)->nullable();
            $table->tinyInteger('has_shoes_rasks')->default(0)->nullable();
            $table->tinyInteger('has_curtain')->default(0)->nullable();
            $table->tinyInteger('has_ceiling_fans')->default(0)->nullable();
            $table->tinyInteger('has_mirror')->default(0)->nullable();
            $table->string('unit')->nullable();
            $table->longText('images')->nullable();
            $table->integer('type')->nullable();
            $table->longText('mo_services')->nullable();
            $table->integer('status')->nullable()->default(0);
            $table->string('note')->nullable();
            $table->tinyInteger('admin_verified')->nullable()->default(0);
            $table->tinyInteger('available_motel')->nullable()->default(0);
            $table->string('link_video')->nullable();
            $table->integer('quantity_vehicle_parked')->nullable()->default(0);
            $table->integer('number_floor')->default(1)->nullable();
            $table->tinyInteger('has_sofa')->nullable()->default(0);
            $table->longText('furniture')->nullable();
            $table->integer('number_calls')->nullable()->default(0);
            $table->double('money_commission_user')->nullable()->default(0);
            $table->double('money_commission_admin')->nullable()->default(0);
            $table->tinyInteger('admin_confirm_commission')->nullable()->default(0);
            $table->integer('percent_commission')->nullable()->default(0);
            $table->integer('percent_commission_collaborator')->nullable()->default(0);

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
        Schema::dropIfExists('mo_post_roommates');
    }
}
