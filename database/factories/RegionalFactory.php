<?php

namespace Database\Factories;

use App\Models\Regional;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Regional>
 */
class RegionalFactory extends Factory
{
    protected $model = Regional::class;

    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->bothify('REG-###'), 
            'name' => $this->faker->city, 
        ];
    }
}
