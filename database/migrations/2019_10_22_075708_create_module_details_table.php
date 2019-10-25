<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateModuleDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('module_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('module_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('company_id');
            $table->tinyInteger('status')->default(0); // Status to enable and disable module_details
            $table->integer('wait_mod_no')->nullable();// Wait with execution until the MOD pointer number is reached
            $table->integer('wait_mod_id')->nullable();// Wait until MOD has successfully completed with ID
            $table->dateTime('last_call')->nullable(); // When was cron job last call 
            $table->tinyInteger('call_count')->default(0); // How many times cron job called
            $table->tinyInteger('run_count')->default(0); // How many times cron runned
            $table->dateTime('last_error')->nullable(); // When was cron failed
            $table->tinyInteger('error_count')->default(0); // How many times cron failed
            $table->integer('max_error')->nullable(); // It is for setting maximum error limit
            $table->tinyInteger('delivery_status')->default(0); // 0 not active, 1 active, 2 wait
            $table->tinyInteger('order_in_logistics')->default(0); // Place order as set order in logistics
            $table->tinyInteger('order_shipped')->default(0); // Declare order as shipped
            $table->tinyInteger('status_mail')->default(0); // Email status
            $table->string('mail_from_name')->nullable();
            $table->unsignedBigInteger('user_id'); // To get supplier name and email
            $table->unsignedBigInteger('user_id'); // To get admin name and email
            $table->string('mail_bcc')->nullable(); 
            $table->string('mail_bcc_name')->nullable();
            $table->string('mail_subject')->nullable();
            $table->text('mail_body')->nullable();
            $table->tinyInteger('mail_attach')->default(0); // Activate delivery note shipping
            $table->tinyInteger('mail_attach_client')->default(0); // Activate customer data sending
            $table->tinyInteger('mail_attach_delivery')->default(0); // Enable delivery address data shipping
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
        Schema::dropIfExists('module_details');
    }
}
