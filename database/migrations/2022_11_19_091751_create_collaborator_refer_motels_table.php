<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCollaboratorReferMotelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('collaborator_refer_motels')) {
            Schema::create('collaborator_refer_motels', function (Blueprint $table) {
                $table->id();

                $table->bigInteger('user_id')->nullable();
                $table->bigInteger('user_referral_id')->nullable();
                $table->bigInteger('contract_id')->nullable();
                $table->bigInteger('motel_id')->nullable();
                $table->timestamp('date_refer_success')->nullable();
                $table->double('money_commission_admin')->nullable()->default(0);
                $table->double('money_commission_user')->nullable()->default(0);
                $table->string('description')->nullable();
                $table->integer('status')->nullable()->default(0);
                $table->integer('status_commission_collaborator')->nullable()->default(0);
                $table->tinyInteger('first_receive_commission')->default(0);

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
        Schema::dropIfExists('collaborator_refer_motels');
    }
}
