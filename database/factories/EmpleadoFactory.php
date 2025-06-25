<?php

namespace Database\Factories;

use App\Models\Empleado;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmpleadoFactory extends Factory
{
    protected $model = Empleado::class;

    public function definition()
    {
        return [
            'nombres' => $this->faker->firstName(),
            'apellidos' => $this->faker->lastName(),
            'telefono' => $this->faker->phoneNumber(),
            'cedula' => $this->faker->unique()->numerify('#########'),
            'tipo_salario' => $this->faker->randomElement(['mensual', 'diario', 'quincenal', 'semanal']),
            'salario' => $this->faker->randomFloat(2, 400, 1200),
        ];
    }
}
