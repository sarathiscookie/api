<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShopsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shops', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('shop', 150);
            $table->unsignedBigInteger('company_id')->nullable();
            $table->string('mail_driver', 150);
            $table->string('mail_host', 150);
            $table->string('mail_port', 20);
            $table->string('mail_from_address');
            $table->string('mail_from_name', 150);
            $table->string('mail_username', 50);
            $table->string('mail_password');
            $table->string('mail_encryption', 20);
            $table->string('customer_number')->nullable();
            $table->string('password')->nullable();
            $table->string('api_key')->nullable();
            $table->enum('active', ['yes', 'no'])->default('no');
            $table->timestamps();

            $table->foreign('company_id')
            ->references('id')->on('companies');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shops');
    }
}
