<?php

namespace Database\Factories;

use App\Models\Egreso;
use Illuminate\Database\Eloquent\Factories\Factory;

class EgresoFactory extends Factory
{
    protected $model = Egreso::class;

    public function definition()
    {
        return [
            'fecha' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'tipo' => $this->faker->randomElement(['salario', 'proveedor', 'gasto_general']),
            'referencia_id' => null, // Se puede poblar luego si se requiere relación
            'monto' => $this->faker->randomFloat(2, 5, 200),
            'descripcion' => $this->faker->sentence(6),
        ];
    }
}
