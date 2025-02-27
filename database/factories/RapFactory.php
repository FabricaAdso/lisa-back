<?php

namespace Database\Factories;

use App\Models\Rap;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Rap>
 */
class RapFactory extends Factory
{
    protected $model = Rap::class;

    public function definition(): array
    {
        return [
            'description' => $this->faker->sentence(), 
            'number_hours' => $this->faker->numberBetween(10, 100), 
            'subject_id' => Subject::factory()->create()->id, 
        ];
    }
}

