<?php

namespace Database\Factories;

use App\Models\EducationLevel;
use App\Models\Program;
use App\Models\TrainingCenter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Program>
 */
class ProgramFactory extends Factory
{
    protected $model = Program::class;

    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->numerify('####'),
            'version' => $this->faker->randomDigitNotZero(),
            'name' => $this->faker->sentence(3), 
            'education_level_id' => EducationLevel::factory(), 
            'training_center_id' => TrainingCenter::factory(), 
        ];
    }
}
