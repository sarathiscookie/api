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
            $table->unsignedBigInteger('module_setting_id');
            $table->tinyInteger('status_mail'); // Email status. 0 = Not sent, 1 = Sent.
            $table->tinyInteger('status')->default(0); // 0 Success, 1 Failed.
            $table->timestamps();

            $table->foreign('module_setting_id')
            ->references('id')->on('module_settings');

            // When was cron job last call.
            // How many times cron job called.
            // When was cron failed.
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
