<?php

namespace Database\Factories;

use App\Models\Headquarters;
use App\Models\TrainingCenter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Headquarters>
 */
class HeadquartersFactory extends Factory
{
    protected $model = Headquarters::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company(), 
            'adress' => $this->faker->address(), 
            'opening_time' => $this->faker->time('H:i:s', '08:00:00'), 
            'closing_time' => $this->faker->time('H:i:s', '18:00:00'), 
            'municipality' => $this->faker->city(), 
            'training_center_id' => TrainingCenter::factory(), 
        ];
    }
}
