<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateTowersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('towers', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id')->unsigned()->index();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->integer('type')->default(0)->nullable(); // 0 phong cho thue, 1 ktx, 2 phong o ghep, 3 nha nguyen can, 4 can ho
            $table->integer("status")->default(2)->nullable(); //0 chờ duyệt, 1 bị từ chối, 2 đồng ý
            $table->boolean('accuracy')->default(false)->nullable(); // dấu tích xác thực
            $table->string('phone_number')->nullable(); // số người liên hệ cho thuê
            $table->longText('description')->nullable(); // nội dung mô tả
            $table->string('tower_name')->nullable(); // tên phòng
            $table->string('tower_name_filter')->nullable(); // tên phòng lọc
            $table->integer('capacity')->default(1)->nullable(); // sức chứa người/phòng
            $table->integer('sex')->default(0)->nullable(); // giới tính 0 tất cả, 1 nam , 2 nữ
            $table->double('area')->default(0)->nullable(); // diện tích m2
            $table->double('money')->default(0)->nullable(); // số tiền thuê vnd/ phòng
            $table->double('min_money')->nullable()->default(0);
            $table->double('max_money')->nullable()->default(0);
            $table->double('deposit')->default(0)->nullable(); // đặt cọc
            $table->double('electric_money')->default(0)->nullable(); // tiền điện - 0 là free
            $table->double('water_money')->default(0)->nullable(); // tiền nước - 0 là free
            $table->boolean('has_wifi')->default(0)->nullable();
            $table->double('wifi_money')->default(0)->nullable(); // tiền wifi - 0 là free
            $table->boolean("has_park")->default(1)->nullable(); //có chỗ để xe không
            $table->double('park_money')->default(0)->nullable(); // phí đỗ xe
            $table->string("video_link")->nullable();
            $table->string("province_name")->nullable();
            $table->string("district_name")->nullable();
            $table->string("wards_name")->nullable();
            $table->integer("province")->nullable();
            $table->integer("district")->nullable();
            $table->integer("wards")->nullable();
            $table->string("address_detail")->nullable();
            $table->boolean('has_wc')->default(0)->nullable();
            $table->boolean('has_window')->default(0)->nullable();
            $table->boolean('has_security')->default(0)->nullable();
            $table->boolean('has_free_move')->default(0)->nullable(); //tự do
            $table->boolean('has_own_owner')->default(0)->nullable(); //chủ riêng
            $table->boolean('has_air_conditioner')->default(0)->nullable();
            $table->boolean('has_water_heater')->default(0)->nullable();
            $table->boolean('has_kitchen')->default(0)->nullable();
            $table->boolean('has_fridge')->default(0)->nullable(); //tủ lạnh
            $table->boolean('has_washing_machine')->default(0)->nullable(); //tủ lạnh
            $table->boolean('has_mezzanine')->default(0)->nullable(); //gác lửng
            $table->boolean('has_bed')->default(0)->nullable(); //giường
            $table->boolean('has_wardrobe')->default(0)->nullable(); //tủ
            $table->boolean('has_tivi')->default(0)->nullable(); //tủ
            $table->boolean('has_pet')->default(0)->nullable(); //thú cưng
            $table->boolean('has_balcony')->default(0)->nullable(); //thú cưng
            $table->boolean('has_ceiling_fans')->default(0)->nullable();
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
            $table->tinyInteger('has_mirror')->default(0)->nullable();
            $table->longText('images')->nullable();
            $table->longText('furniture')->nullable();
            $table->tinyInteger('admin_verified')->default(0)->nullable();
            $table->tinyInteger('has_post')->default(0)->nullable();
            $table->integer('number_floor')->default(1)->nullable();
            $table->integer('quantity_vehicle_parked')->default(0)->nullable();
            $table->tinyInteger('has_sofa')->default(0)->nullable();
            $table->tinyInteger('has_contract')->default(1)->nullable();
            $table->tinyInteger('percent_commission')->default(0)->nullable();
            $table->tinyInteger('percent_commission_collaborator')->default(0)->nullable();
            $table->tinyInteger('money_commission_admin')->default(0)->nullable();
            $table->tinyInteger('money_commission_user')->default(0)->nullable();

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
        Schema::dropIfExists('towers');
    }
}
