<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVirtualAccountTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('virtual_account_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('payment_no')->nullable();
            $table->string('invoice_no')->nullable();
            $table->string('currency')->nullable();
            $table->decimal('amount', 14, 2)->nullable();
            $table->text('description')->nullable();
            $table->string('method')->nullable();
            $table->string('card_brand')->nullable();
            $table->string('payment_status')->default(\App\Models\VirtualAccount::PAYMENT_PROCESSING);
            $table->timestamp('payment_at')->nullable();
            $table->json('card_info')->nullable();
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
        Schema::dropIfExists('virtual_account_transactions');
    }
}
