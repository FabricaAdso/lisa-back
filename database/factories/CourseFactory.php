<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Environment;
use App\Models\Program;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Course>
 */
class CourseFactory extends Factory
{
    protected $model = Course::class;

    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->numerify('C####'), 
            'date_start' => $this->faker->date(),
            'date_end' => $this->faker->date(),
            'shift' => $this->faker->randomElement(['Mañana', 'Tarde', 'Noche']),
            'state' => $this->faker->randomElement(['Terminada_por_fecha', 'En_ejecucion', 'Terminada', 'Termindad_por_unificacion']),
            'stage' => $this->faker->randomElement(['PRACTICA', 'LECTIVA']),
            'program_id' => Program::factory(), 
            'environment_id' => Environment::factory(), 
        ];
    }
}
