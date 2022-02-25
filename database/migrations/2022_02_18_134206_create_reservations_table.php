<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReservationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('nursery_id');
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('child_id');
            $table->tinyInteger('status')->nullable();
            $table->tinyInteger('provider_end')->default(0);
            $table->tinyInteger('client_end')->default(0);
            $table->text('reply')->nullable();
            $table->json('activities')->nullable();
            $table->json('courses')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('child_id')
            ->references('id')
            ->on('children')
            ->onUpdate('cascade')
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
        Schema::dropIfExists('reservations');
    }
}
