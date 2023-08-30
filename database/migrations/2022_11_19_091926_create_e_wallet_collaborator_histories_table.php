<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEWalletCollaboratorHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('e_wallet_collaborator_histories')) {
            Schema::create('e_wallet_collaborator_histories', function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('e_wallet_collaborator_id')->unsigned()->index();
                $table->foreign('e_wallet_collaborator_id')->references('id')->on('e_wallet_collaborators')->cascadeOnDelete();
                $table->bigInteger('value_reference')->nullable();
                $table->double('balance_origin')->nullable()->default(0);
                $table->double('money_change')->nullable()->default(0);
                $table->double('account_balance_changed')->nullable()->default(0);
                $table->string('title')->nullable();
                $table->string('description')->nullable();
                $table->integer('type_money_from')->nullable();
                $table->tinyInteger('take_out_money')->nullable()->default(0);

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
        Schema::dropIfExists('e_wallet_collaborator_histories');
    }
}
