<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSystemPermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('system_permissions')) {
            Schema::create('system_permissions', function (Blueprint $table) {
                $table->id();

                $table->string('name')->nullable();
                $table->string('description')->nullable();
                $table->tinyInteger('view_badge')->nullable()->default(0);
                $table->tinyInteger('manage_motel')->nullable()->default(0);
                $table->tinyInteger('manage_user')->nullable()->default(0);
                $table->tinyInteger('manage_mo_post')->nullable()->default(0);
                $table->tinyInteger('manage_contract')->nullable()->default(0);
                $table->tinyInteger('manage_bill')->nullable()->default(0);
                $table->tinyInteger('manage_message')->nullable()->default(0);
                $table->tinyInteger('manage_report_problem')->nullable()->default(0);
                $table->tinyInteger('manage_service')->nullable()->default(0);
                $table->tinyInteger('manage_order_service_sell')->nullable()->default(0);
                $table->tinyInteger('manage_notification')->nullable()->default(0);
                $table->tinyInteger('setting_banner')->nullable()->default(0);
                $table->tinyInteger('setting_contact')->nullable()->default(0);
                $table->tinyInteger('setting_help')->nullable()->default(0);
                $table->tinyInteger('manage_motel_consult')->nullable()->default(0);
                $table->tinyInteger('manage_report_statistic')->nullable()->default(0);
                $table->tinyInteger('all_access')->nullable()->default(0);
                $table->tinyInteger('manage_service_sell')->nullable()->default(0);
                $table->tinyInteger('setting_category_help')->nullable()->default(0);
                $table->tinyInteger('able_decentralization')->nullable()->default(0);
                $table->tinyInteger('unable_access')->nullable()->default(0);
                $table->tinyInteger('able_remove')->nullable()->default(1);
                $table->tinyInteger('manage_renter')->nullable()->default(0);
                $table->tinyInteger('manage_collaborator')->nullable()->default(0);

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
        Schema::dropIfExists('system_permissions');
    }
}
