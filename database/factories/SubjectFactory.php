<?php

namespace Database\Factories;

use App\Models\Program;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subject>
 */
class SubjectFactory extends Factory
{
    protected $model = Subject::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word . ' Avanzado', 
            'total_number_hours' => $this->faker->numberBetween(40, 160), 
            'program_id' => Program::factory(), 
        ];
    }
}
