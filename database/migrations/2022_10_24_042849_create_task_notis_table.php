<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaskNotisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('task_notis')) {
            Schema::create('task_notis', function (Blueprint $table) {
                $table->id();

                $table->string('title')->nullable();
                $table->string('description')->nullable();
                $table->time('time_of_day')->nullable()->default('00:00:00');
                $table->integer('type_schedule')->nullable();
                $table->timestamp('time_run')->nullable();
                $table->integer('day_of_week')->nullable();
                $table->integer('day_of_month')->nullable();
                $table->timestamp('time_run_near')->nullable();
                $table->integer('status')->nullable();
                $table->string('reminiscent_name')->nullable();
                $table->string('type_action')->nullable();
                $table->string('value_action')->nullable();
                $table->integer('role')->nullable();
                $table->string('reference_value')->nullable();

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
        Schema::dropIfExists('task_notis');
    }
}
