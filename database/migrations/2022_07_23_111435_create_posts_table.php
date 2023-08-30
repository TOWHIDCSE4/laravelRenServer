<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('posts')) {
            Schema::create('posts', function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('motel_id')->unsigned()->index();
                $table->foreign('motel_id')->references('id')->on('motels')->onDelete('cascade');

                $table->string('phone_number')->nullable(); // số người liên hệ
                $table->string('title')->nullable(); // tiêu đề
                $table->longText('description')->nullable(); // nội dung mô tả

                $table->double("price")->double(0)->nullable();

                $table->string("province_name")->nullable();
                $table->string("district_name")->nullable();
                $table->string("wards_name")->nullable();

                $table->integer("province")->nullable();
                $table->integer("district")->nullable();
                $table->integer("wards")->nullable();
                $table->integer('status')->nullable();
                $table->string("address_detail")->nullable();

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
        Schema::dropIfExists('posts');
    }
}
