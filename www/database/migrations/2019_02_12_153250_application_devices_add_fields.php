<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ApplicationDevicesAddFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('application_devices', function (Blueprint $table) {
            $table->string('bundle_version')->nullable();
            $table->json('extinfo')->nullable();
            $table->string('bundle_short_version')->nullable();
            $table->string('application_tracking_enabled')->nullable();
            $table->string('advertiser_tracking_enabled')->nullable();
            $table->string('attribution')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('application_devices', function (Blueprint $table) {
            $table->dropColumn('bundle_version');
            $table->dropColumn('extinfo');
            $table->dropColumn('bundle_short_version');
            $table->dropColumn('application_tracking_enabled');
            $table->dropColumn('advertiser_tracking_enabled');
            $table->dropColumn('attribute');
        });
    }
}
