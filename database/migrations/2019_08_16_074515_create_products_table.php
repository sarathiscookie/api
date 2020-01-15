<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('api_product_id')->unique(); // Product id from API
            $table->unsignedBigInteger('shopname_id');
            $table->unsignedBigInteger('company_id');
            $table->string('product_art_no', 15)->nullable();
            $table->string('name', 100)->nullable();
            $table->string('producer', 30)->nullable();
            $table->decimal('price', 8, 2)->nullable();
            $table->decimal('price_reduced', 8, 2)->nullable();
            $table->string('price_reduced_type', 15)->nullable();
            $table->integer('shipping_group')->nullable();
            $table->integer('tax')->nullable();
            $table->string('baseprice_unit', 15)->nullable();
            $table->decimal('baseprice_volume', 8, 2)->nullable();
            $table->integer('min_order_qty')->nullable();
            $table->boolean('stock_policy')->nullable();
            $table->integer('stock')->nullable();
            $table->integer('delivery')->nullable();
            $table->integer('visible')->nullable();
            $table->boolean('available')->nullable();
            $table->boolean('homepage')->nullable();
            $table->boolean('connect')->nullable();
            $table->string('isbn', 25)->nullable();
            $table->string('ean', 25)->nullable();
            $table->string('mpn', 25)->nullable();
            $table->text('description')->nullable();
            $table->string('inci', 50)->nullable();
            $table->string('comment')->nullable();
            $table->string('cross_selling_title', 100)->nullable();
            $table->tinyInteger('module_status')->default(0); // Status to enable and disable product_module_details
            $table->timestamps();

            $table->foreign('shopname_id')
            ->references('id')->on('shopnames');

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
        Schema::dropIfExists('products');
    }
}
