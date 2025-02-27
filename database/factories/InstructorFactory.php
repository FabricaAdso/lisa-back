<?php

namespace Database\Factories;

use App\Models\Instructor;
use App\Models\KnowledgeNetwork;
use App\Models\TrainingCenter;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Instructor>
 */
class InstructorFactory extends Factory
{
    protected $model = Instructor::class;

    public function definition(): array
    {
        return [
            'state' => $this->faker->randomElement(['Activo', 'Inactivo']),
            'user_id' => User::factory()->create()->id, 
            'training_center_id' => TrainingCenter::factory()->create()->id,
            'knowledge_network_id' => KnowledgeNetwork::factory()->create()->id,
        ];
    }
    
}
