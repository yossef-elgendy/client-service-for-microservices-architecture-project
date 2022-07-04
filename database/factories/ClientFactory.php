<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    protected $model = Client::class;
    
    public function definition()
    {
        $toss = $this->faker->boolean;
        $email = null;
        $phone = null;
    
        if($toss) {
          $email = $this->faker->unique()->safeEmail;
          $login_type = 'EM';
        } else {
          $phone = $this->faker->unique()->regexify('^01[0125][0-9]{8}$');
          $login_type = 'PH';
        }
    
        return [
          'id' => $this->faker->unique()->randomNumber(2, true),
          'username' => $this->faker->unique()->userName,
          'fullname' => $this->faker->name(),
          'email' => $email,
          'phone' => $phone,
          'login_type' => $login_type,
          'status' => $this->faker->randomElement(array_keys(Client::STATUS)),
          'gender' => $this->faker->randomElement(array_keys(Client::GENDER)),
          
        ];
    }
    
}
