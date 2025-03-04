<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Instructor;
use App\Models\Rap;
use App\Models\Session;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Session>
 */
class SessionFactory extends Factory
{
    protected $model = Session::class;

    public function definition(): array
    {
        return [
            'date' => $this->faker->date(),
            'start_time' => $this->faker->time(),
            'end_time' => $this->faker->time(),
            'instructor_id' => Instructor::find(1),
            'rap_id' => Rap::factory()->create()->id, 
            'instructor2_id' => Instructor::factory()->create()->id, 
            'course_id' => Course::factory()->create()->id, 
        ];
    }

}
