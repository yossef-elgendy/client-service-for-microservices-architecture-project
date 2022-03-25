<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('paymob_order_id')->nullable();
            $table->unsignedBigInteger('reservation_id');
            $table->tinyInteger('status')->default(0);
            $table->text('payment_key')->nullable();
            $table->float('totalCost', 9, 2);
            $table->timestamps();

            $table->foreign('reservation_id')
            ->references('id')->on('reservations')
            ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
