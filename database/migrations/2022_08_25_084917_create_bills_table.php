<?php

use App\Helper\Helper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateBillsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('bills')) {
            Schema::create('bills', function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('contract_id')->unsigned()->index();
                $table->unsignedBigInteger('service_close_id')->unsigned()->index()->nullable();
                $table->integer('status')->nullable()->default(0);
                $table->timestamp('date_payment')->default(DB::raw('CURRENT_TIMESTAMP'));
                $table->double('total_money_motel')->nullable()->default(0);
                $table->double('total_money_service')->nullable()->default(0);
                $table->double('total_final')->nullable()->default(0);
                $table->double('discount')->nullable()->default(0);
                $table->double('deposit_money')->nullable()->default(0);
                $table->longText('images')->nullable();
                $table->string('content')->nullable();
                $table->string('note')->nullable();
                $table->integer('type_bill')->nullable()->default(0);
                $table->longText('bill_log')->nullable();
                $table->tinyInteger('is_init')->default(0)->nullable();
                $table->double('total_money_has_paid_by_deposit')->nullable()->default(0);
                $table->tinyInteger('has_use_deposit')->nullable()->default(0);
                $table->double('total_money_before_paid_by_deposit')->nullable()->default(0);

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
        Schema::dropIfExists('bills');
    }
}
