<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DuplicateGroupFactory extends Factory
{
    public function definition(): array
    {
        return [
            'signature' => $this->faker->uuid(),
        ];
    }
}