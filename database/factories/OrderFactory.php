<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */

    protected $table = 'orders';

    public function definition()
    {
        return [
            'totalCost' => $this->faker->randomFloat(2,1000,500)
        ];
    }
}
