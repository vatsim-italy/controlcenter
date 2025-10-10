<?php

namespace Database\Factories;

use App\Models\Evaluation;
use App\Models\Training;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EvaluationFactory extends Factory
{
    protected $model = Evaluation::class;

    public function definition()
    {
        $startTime = $this->faker->time('H:i');
        $endTime = $this->faker->time('H:i', $startTime);

        return [
            'student_id' => User::factory(), // Creates a new user by default
            'level' => $this->faker->randomElement(['S1', 'S2', 'S3', 'C1']),
            'date' => $this->faker->dateTimeBetween('-1 years', 'now')->format('Y-m-d'),
            'examiner_id' => User::factory(),
            'training_id' => Training::factory(),
            'position' => $this->faker->word(),
            'start' => $startTime,
            'end' => $endTime,
            'sessionPerformed' => $this->faker->randomElement(['Yes', 'No']),
            'complexity' => $this->faker->numberBetween(1, 5),
            'workload' => $this->faker->numberBetween(1, 5),
            'trafficLoad' => $this->faker->numberBetween(1, 5),
            'trainingPhase' => $this->faker->word(),
            'finalReview' => $this->faker->sentence(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
