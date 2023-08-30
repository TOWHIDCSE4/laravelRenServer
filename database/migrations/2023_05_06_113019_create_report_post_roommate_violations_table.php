<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportPostRoommateViolationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('report_post_roommate_violations', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('mo_post_roommate_id')->nullable();
            $table->string('reason')->nullable();
            $table->text('description')->nullable();
            $table->bigInteger('user_id')->nullable();
            $table->integer('status')->nullable()->default(0);

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
        Schema::dropIfExists('report_post_roommate_violations');
    }
}
