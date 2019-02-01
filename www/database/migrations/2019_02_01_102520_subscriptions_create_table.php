<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SubscriptionsCreateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->string('id');
            $table->integer('application_id');
            $table->timestamps();
            $table->integer('user_id')->nullable();
            $table->string('device_id');
            $table->string('product_id');
            $table->string('environment');
            $table->string('original_transaction_id');
            $table->string('type');
            $table->bigInteger('start_date');
            $table->bigInteger('end_date');
            $table->text('latest_receipt');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subscriptions');
    }
}
