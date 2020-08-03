<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateModuleSettingsTable extends Migration
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
            $table->unsignedBigInteger('product_id'); // Product id from API
            
            // Email settings
            $table->unsignedBigInteger('user_supplier_id')->nullable(); // To get supplier name and email. Email send to supplier then cc to admin.
            $table->string('mail_bcc')->nullable(); // Write bcc details manually.
            $table->string('mail_bcc_name', 150)->nullable();
            $table->string('mail_subject', 200);
            $table->text('mail_body');
            $table->tinyInteger('setOrderShipped')->nullable(); // This is an API order feature. This methods sets an order to shipped. 1 = checked, 0 = not checked.
            $table->tinyInteger('setOrderLogistic')->nullable(); // This is an API feature. Use this method to set an order to “in preparation for shipping”. This disables the option for the customer or Rakuten customer service to cancel an order for 48 hours. 1 = checked, 0 = not checked.
            $table->tinyInteger('getOrderDeliveryNote')->nullable(); // This is an API feature. This method gives out the delivery note to an order. 1 = checked, 0 = not checked.

            // Orders & Delivery settings
            $table->tinyInteger('order_in_logistics')->nullable(); // Place order as set order in logistics. 1 = checked, 0 = not checked.
            $table->tinyInteger('order_shipped')->nullable(); // Declare order as shipped. 1 = checked, 0 = not checked.
            
            $table->tinyInteger('status')->default(1); // 0 => Deactive & 1 => Active.
            $table->timestamps();

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
        Schema::dropIfExists('module_settings');
    }
}
