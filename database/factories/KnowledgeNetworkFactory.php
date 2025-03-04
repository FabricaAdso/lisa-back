<?php

namespace Database\Factories;

use App\Models\KnowledgeNetwork;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\KnowledgeNetwork>
 */
class KnowledgeNetworkFactory extends Factory
{
    protected $model = KnowledgeNetwork::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word . ' Network', 
        ];
    }
}
