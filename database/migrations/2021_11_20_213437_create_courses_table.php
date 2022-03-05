<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoursesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('nursery_id');
            $table->string('name');
            $table->text('description');
            $table->json('age_range')->nullable();
            $table->float('cost', 6, 2);
            $table->float('rate', 3, 2, true)->nullable();
            $table->timestamps();

            $table->foreign('nursery_id')->references('id')->on('nurseries')
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
        Schema::dropIfExists('courses');
    }
}
