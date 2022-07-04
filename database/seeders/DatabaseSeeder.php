<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Review;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
       Client::factory(10)->create();
       Review::factory(10)->create();
    }
}
