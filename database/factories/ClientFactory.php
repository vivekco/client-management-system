<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ClientFactory extends Factory
{
    public function definition(): array
    {
        $email = $this->faker->unique()->safeEmail();
        $phone = (string) $this->faker->unique()->numberBetween(9800000000, 9899999999);

        return [
            'company_name' => $this->faker->company(),
            'email' => $email,
            'phone_number' => $phone,
            'signature' => md5(strtolower($this->faker->company() . '|' . $email . '|' . $phone)),
            'duplicate_group_id' => null,
        ];
    }
}