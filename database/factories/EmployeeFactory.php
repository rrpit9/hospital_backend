<?php

namespace Database\Factories;

use Hash;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class EmployeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'business_id' => 1,
            'client_id' => 1,
            'name' => $this->faker->name(),
            'mobile' => rand(6000000001,9999999999),
            'mobile_verified_at' => now(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make(generateUniqueAlphaNumeric(8)),
            'remember_token' => Str::random(10),
            'gender' => gender()[rand(0,2)],
            'dob' => null,
            'marital' => marital()[rand(0,2)],
            'aniversary' => null,
            'is_registered' => true,
            'active' => true
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function unverified()
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
                'mobile_verified_at' => null
            ];
        });
    }
}
