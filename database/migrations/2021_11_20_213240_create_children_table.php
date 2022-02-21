<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChildrenTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('children', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('nursery_id')->nullable();  // updated 17/2/2022
            $table->unsignedBigInteger('client_id');
            $table->string('name', 30);// new 19/2/2022
            $table->tinyInteger('age'); // new 17/2/2022
            $table->json('time_table')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->tinyInteger('gender')->default(1); // updated 17/2/2022
            $table->float('rate', 3, 2, true)->nullable(); // updated 17/2/2022
            $table->json('marks')->nullable(); // updated 17/2/2022
            $table->text('issues')->nullable(); // updated 17/2/2022
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('children');
    }
}
