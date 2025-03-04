<?php

namespace Database\Factories;

use App\Models\Regional;
use App\Models\TrainingCenter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TrainingCenter>
 */
class TrainingCenterFactory extends Factory
{
    protected $model = TrainingCenter::class;

    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->bothify('TC-####'), 
            'name' => $this->faker->company, 
            'regional_id' => Regional::factory()->create()->id, 
        ];
    }
}
