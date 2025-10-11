<?php

namespace Database\Factories;

use App\Models\EvaluationItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class EvaluationItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = EvaluationItem::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $date = $this->faker->dateTimeBetween($startDate = '-1 years', $endDate = 'now');

        return [
            'rating' => 'S1',
            'category' => $this->faker->randomElement(['GENERAL', 'ATC_COORDINATION']),
            'key_name' => $this->faker->words(3, true),
            'description' => $this->faker->words(3, true),
            'created_at' => $date,
            'updated_at' => $date,
        ];
    }
}
