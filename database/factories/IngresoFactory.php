<?php

namespace Database\Factories;

use App\Models\Ingreso;
use Illuminate\Database\Eloquent\Factories\Factory;

class IngresoFactory extends Factory
{
    protected $model = Ingreso::class;

    public function definition()
    {
        return [
            'fecha' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'tipo' => $this->faker->randomElement(['lavado', 'producto_automotriz', 'producto_despensa']),
            'referencia_id' => null, // Se puede poblar luego si se requiere relación
            'monto' => $this->faker->randomFloat(2, 5, 200),
            'descripcion' => $this->faker->sentence(6),
        ];
    }
}
