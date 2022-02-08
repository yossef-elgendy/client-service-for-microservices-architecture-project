<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNurseriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nurseries', function (Blueprint $table) {
            $table->id();
            $table->string('nursery_name');
            $table->json('location');
            $table->json('active_hours');
            $table->integer('status');
            $table->float('subscription_fee', 7, 2);
            $table->float('rate', 3, 2, true);
            $table->json('social_links');
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
        Schema::dropIfExists('nurseries');
    }
}
