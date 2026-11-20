<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVendorOrdersTable extends Migration
{
    public function up()
    {
        Schema::create('vendor_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('order_id')->nullable()->index();   // orders.id
            $table->unsignedBigInteger('product_id')->nullable();          // posts.id
            $table->unsignedBigInteger('vendor_user_id')->nullable();      // users.id (role 8)
            $table->string('shop_domain')->nullable();
            $table->string('shopify_order_id')->nullable();
            $table->string('status', 40);   // created | skipped_parent_only | skipped_no_token | failed_*
            $table->text('error')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('vendor_orders');
    }
}
