<?php


namespace Database\Factories;

use App\Models\Area;
use App\Models\Position;
use App\Models\Rating;
use Illuminate\Database\Eloquent\Factories\Factory;

class PositionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Position::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $date = $this->faker->dateTimeBetween(
            '-1 years',
            'now'
        );

        return [
            'callsign' => 'LIXX_TWR',
            'name' => 'LIXX',
            'frequency' => '123.450',
            'fir' => 'LIXX',
            'rating' => Rating::factory(),
            'area_id' => Area::factory(),
        ];
    }
}
