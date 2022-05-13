<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('child_id');
            $table->unsignedBigInteger('nursery_id');
            $table->unsignedBigInteger('reservation_id');
            $table->dateTime('start_date');
            $table->dateTime('due_date');
            $table->dateTime('payment_date')->nullable();
            $table->tinyInteger('payment_method')->nullable();
            $table->tinyInteger('status')->default('0');
            $table->timestamps();
            $table->softDeletes();

            // Relations
            $table->foreign('child_id')
              ->references('id')
              ->on('children')
              ->onUpdate('cascade')
              ->onDelete('restrict');
            $table->foreign('reservation_id')
              ->references('id')
              ->on('reservations')
              ->onUpdate('cascade')
              ->onDelete('restrict');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subscriptions');
    }
}
