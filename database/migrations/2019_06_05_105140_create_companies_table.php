<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('company');
            $table->unsignedBigInteger('country_id')->nullable();
            $table->string('street')->nullable();
            $table->string('postal', 20)->nullable();
            $table->string('city')->nullable();
            $table->string('phone', 20)->nullable();
            $table->enum('active', ['yes', 'no'])->default('no');
            $table->timestamps();

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
        Schema::dropIfExists('companies');
    }
}
