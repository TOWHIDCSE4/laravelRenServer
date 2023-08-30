<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportProblemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('report_problems')) {
            Schema::create('report_problems', function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('user_id')->unsigned()->index();
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
                $table->unsignedBigInteger('motel_id')->unsigned()->index();
                $table->foreign('motel_id')->references('id')->on('motels')->cascadeOnDelete();
                $table->string('reason')->nullable();
                $table->string('describe_problem')->nullable();
                $table->integer('status')->default(0)->nullable();
                $table->integer('severity')->default(0)->nullable();
                $table->longText('images')->nullable();
                $table->string('link_video')->nullable();
                $table->timestamp('time_done')->nullable();

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
        Schema::dropIfExists('report_problems');
    }
}
