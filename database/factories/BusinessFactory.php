<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BusinessFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'client_id' => rand(1,5),
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'mobile' => rand(6000000001,9999999999),
            'address' => $this->faker->address,
            'pincode' => \Faker\Provider\Address::postcode(),
            'valid_till' => date('Y-m-d', strtotime('+1 year')),
            'active' => true
        ];
    }
}
