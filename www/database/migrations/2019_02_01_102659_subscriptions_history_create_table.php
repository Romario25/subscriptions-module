<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SubscriptionsHistoryCreateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscriptions_history', function (Blueprint $table) {
            $table->string('id');
            $table->string('subscription_id');
            $table->timestamps();
            $table->string('product_id');
            $table->string('transaction_id');
            $table->string('environment');
            $table->bigInteger('start_date');
            $table->bigInteger('end_date');
            $table->string('type');
            $table->integer('count')->default(0);
            $table->text('receipt')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subscriptions_history');
    }
}
