<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportPostViolationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('report_post_violations')) {
            Schema::create('report_post_violations', function (Blueprint $table) {
                $table->id();

                $table->bigInteger('mo_post_id')->nullable();
                $table->string('reason')->nullable();
                $table->text('description')->nullable();
                $table->bigInteger('user_id')->nullable();
                $table->integer('status')->nullable()->default(0);

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
        Schema::dropIfExists('report_post_violations');
    }
}
