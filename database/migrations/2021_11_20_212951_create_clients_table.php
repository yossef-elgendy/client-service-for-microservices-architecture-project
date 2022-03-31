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
            $table->unsignedBigInteger('id')->primary();
            $table->string('username')->unique()->nullable();
            $table->string('fullname')->nullable();
            $table->string('mobile_number')->unique()->nullable();
            $table->tinyInteger('status')->default('0');
            $table->tinyInteger('gender')->default('0');
            $table->json('location')->nullable(); // {country, city, area}
            $table->json('payment_info')->nullable(); // {creditcard, paypal, ....}
            $table->rememberToken();
            $table->timestamp('email_verified_at')->nullable();
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
