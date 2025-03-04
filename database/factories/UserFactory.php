<?php

namespace Database\Factories;

use App\Models\DocumentType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'identity_document' => $this->faker->unique()->randomNumber(8, true),
            'name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'deactivation_date' => null,
            'is_superuser' => false,
            'email' => $this->faker->unique()->safeEmail,
            'email_verified_at' => now(),
            'password' => bcrypt('password'), 
            'document_type_id' => DocumentType::factory(),
            'remember_token' => Str::random(10),
        ];
    }
}
