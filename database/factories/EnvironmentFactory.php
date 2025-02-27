<?php

namespace Database\Factories;

use App\Models\Environment;
use App\Models\Headquarters;
use App\Models\KnowledgeNetwork;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Environment>
 */
class EnvironmentFactory extends Factory
{
    protected $model = Environment::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word() . ' Lab', 
            'capacity' => $this->faker->numberBetween(10, 50),
            'headquarters_id' => Headquarters::factory(), 
            'knowledge_network_id' => KnowledgeNetwork::factory(), 
        ];
    }
}
