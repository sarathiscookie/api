<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateKeyInstructionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('key_instructions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('key_container_id');
            $table->unsignedBigInteger('country_id');
            $table->string('instruction_url');
            $table->enum('active', ['yes', 'no'])->default('no');
            $table->timestamps();

            $table->foreign('key_container_id')
            ->references('id')->on('key_containers');

            $table->foreign('country_id')
            ->references('id')->on('countries');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('key_instructions');
    }
}
