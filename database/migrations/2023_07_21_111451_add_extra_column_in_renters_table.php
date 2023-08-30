<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExtraColumnInRentersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('renters', function (Blueprint $table) {
            $table->timestamp('date_of_birth')->nullable()->after('is_hidden');
            $table->timestamp('date_range')->nullable()->after('date_of_birth');
            $table->integer("sex")->default(0)->nullable()->after('date_range');
            $table->string('job')->nullable()->after('sex');
            $table->string('type')->nullable()->after('job');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('renters', function (Blueprint $table) {
            $table->dropColumn('date_of_birth');
            $table->dropColumn('date_range');
            $table->dropColumn('sex');
            $table->dropColumn('job');
            $table->dropColumn('type');
        });
    }
}