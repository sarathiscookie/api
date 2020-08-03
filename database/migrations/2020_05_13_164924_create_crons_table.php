<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCronsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('crons', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('module_setting_id')->nullable();
            $table->string('api_order_no', 50); // Order number from API.
            $table->tinyInteger('cron_status')->default(0); // Cron job status. 0 = Cron job not executed, 1 = Cron job already executed.
            $table->timestamps();

            $table->foreign('module_setting_id')
            ->references('id')->on('module_settings');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('crons');
    }
}
