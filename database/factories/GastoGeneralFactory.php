<?php

namespace Database\Factories;

use App\Models\GastoGeneral;
use Illuminate\Database\Eloquent\Factories\Factory;

class GastoGeneralFactory extends Factory
{
    protected $model = GastoGeneral::class;

    public function definition()
    {
        return [
            'nombre' => $this->faker->words(2, true),
            'descripcion' => $this->faker->sentence(6),
            'monto' => $this->faker->randomFloat(2, 10, 300),
            'fecha' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
