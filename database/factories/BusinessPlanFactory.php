<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BusinessPlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'type' => $this->faker->name(),
            'validity' => rand(1,5),
            'description' => $this->faker->address,
            'price' => rand(600, 2000),
            'discount_percentage' => rand(10, 30),
            'is_displayable' => rand(0, 1),
            'active' => true
        ];
    }
}
