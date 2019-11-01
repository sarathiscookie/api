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
        Schema::create('module_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('module_id');
            $table->unsignedBigInteger('product_id');
            
            // Email settings
            $table->unsignedBigInteger('user_supplier_id'); // To get supplier name and email. Email send to supplier then cc to admin.
            $table->string('mail_bcc')->nullable(); // Write bcc details manually.
            $table->string('mail_bcc_name', 150)->nullable();
            $table->string('mail_subject', 200)->nullable();
            $table->text('mail_body')->nullable();
            $table->tinyInteger('mail_attach')->default(0); // Activate delivery note shipping
            $table->tinyInteger('mail_attach_client')->default(0); // Activate customer data sending
            $table->tinyInteger('mail_attach_delivery')->default(0); // Enable delivery address data shipping
            $table->tinyInteger('status_mail')->default(0); // Email status

            //Cron settings
            $table->dateTime('last_call')->nullable(); // When was cron job last call. Data will update after cron run.
            $table->tinyInteger('call_count')->default(0); // How many times cron job called. Data will update after cron run.
            $table->tinyInteger('run_count')->default(0); // How many times cron runned. Data will update after cron run.
            $table->dateTime('last_error')->nullable(); // When was cron failed. Data will update after cron run.
            $table->tinyInteger('error_count')->default(0); // How many times cron failed. Data will update after cron run.
            $table->integer('max_error')->nullable(); // It is for setting maximum error limit. Data will update after cron run.

            //Orders & Delivery settings
            $table->tinyInteger('order_in_logistics')->default(0); // Place order as set order in logistics
            $table->tinyInteger('order_shipped')->default(0); // Declare order as shipped
            $table->integer('delivery_status')->default(0); // 0 not active, 1 active, 2 wait

            //MOD Settings
            $table->integer('wait_mod_no')->nullable(); // Wait with execution until the MOD pointer number is reached.
            $table->integer('wait_mod_id')->nullable(); // Wait until MOD has successfully completed with ID.

            $table->tinyInteger('status')->default(0); // Status to enable and disable module_details
            $table->timestamps();

            $table->foreign('product_id')
            ->references('id')->on('products');

            $table->foreign('module_id')
            ->references('id')->on('modules');

            $table->foreign('user_supplier_id')
            ->references('id')->on('users');
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
