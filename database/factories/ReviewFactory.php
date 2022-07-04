<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Review;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewFactory extends Factory
{

    protected $model = Review::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
       
        $client_ids = Client::pluck('id');

        $contents = [
            'This one really is amazing',
            'This one is absluote genuis',
            'It was not what i expected',
            'Really grateful that I found this on this great website',
            'Nice one',
            'Great one',
            'Not that bad',
            'Awesome work',
            'My kid had a nice time there',
            'Just look at the rete and you will know'
        ];
        
        return [
            'id' => $this->faker->unique()->randomNumber(2, true),
            'client_id' => $this->faker->randomElement($client_ids),
            'model_id' => $this->faker->numberBetween($min = 1, $max = 9),
            'model_type'=> $this->faker->randomElement(array_values(Review::TYPE)),
            'content'=> $this->faker->randomElement($contents),
            'rate'=>$this->faker->numberBetween($min = 3, $max = 5)
        ];
    }
}
