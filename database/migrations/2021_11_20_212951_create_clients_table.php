<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('username');
            $table->string('password');
            $table->string('full_name');
            $table->string('email');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('mobile_number');
            $table->tinyInteger('status')->default('0');
            $table->tinyInteger('gender')->default('0');
            $table->json('location'); // {country, city, area}
            $table->json('payment_info'); // {creditcard, paypal, ....}
            $table->rememberToken();
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
        Schema::dropIfExists('clients');
    }
}
