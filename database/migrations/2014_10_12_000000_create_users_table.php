<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();

                $table->string('area_code')->nullable();
                $table->string('phone_number')->unique()->nullable();
                $table->timestamp('phone_verified_at')->nullable();
                $table->string('email')->unique()->nullable();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->string('name')->nullable();
                $table->timestamp('date_of_birth')->nullable();
                $table->string('avatar_image')->nullable();
                $table->integer("sex")->default(0)->nullable();
                $table->integer("permission")->default(0)->nullable(); //0 khách thường  // 1nv sale // 2 Admin
                $table->rememberToken();
                $table->integer("status")->nullable()->default(2);
                $table->tinyInteger("is_host")->nullable(); //phải chủ nhà không
                $table->tinyInteger("is_admin")->default(0)->nullable();
                $table->integer("host_rank")->default(0)->nullable();
                $table->text('social_id')->nullable();
                $table->string('social_from')->nullable();
                $table->tinyInteger('has_post')->nullable()->default(0);
                $table->tinyInteger('account_rank')->nullable()->default(0);
                $table->tinyInteger('service_default')->nullable()->default(0);
                $table->tinyInteger('is_choosed_decent')->nullable()->default(0);
                $table->tinyInteger('is_authorized')->nullable()->default(0);
                $table->string('referral_code')->nullable();
                $table->string('self_referral_code')->nullable();
                $table->tinyInteger('has_referral_code')->nullable()->default(0);
                $table->string('cmnd_number')->nullable();
                $table->string('cmnd_front_image_url')->nullable();
                $table->string('cmnd_back_image_url')->nullable();
                $table->string('bank_account_number')->nullable();
                $table->string('bank_account_name')->nullable();
                $table->string('bank_name')->nullable();
                $table->tinyInteger('initial_account_type')->nullable()->default(0);

                $table->timestamps();
            });

            User::create(
                [
                    'area_code' => "+84",
                    'phone_number' => "0123456789",
                    'email' => "test@gmail.com",
                    'password' => bcrypt("123")
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
