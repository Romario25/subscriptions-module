<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ApplicationsCreateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('bundle_id');
            $table->string('app_id');
            $table->string('name');
            $table->string('environment');
            $table->smallInteger('send_stat_appsflyer')->default(0);
            $table->smallInteger('send_stat_facebook')->default(0);
            $table->string('appsflyer_dev_key')->nullable();
            $table->string('shared_secret')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('applications', function (Blueprint $table) {
           $table->dropIfExists('applications');
        });
    }
}
