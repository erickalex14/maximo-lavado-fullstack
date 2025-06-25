<?php

namespace Database\Factories;

use App\Models\Proveedor;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProveedorFactory extends Factory
{
    protected $model = Proveedor::class;

    public function definition()
    {
        return [
            'nombre' => $this->faker->company(),
            'email' => $this->faker->unique()->safeEmail(),
            'descripcion' => $this->faker->sentence(6),
            'telefono' => $this->faker->phoneNumber(),
            'deuda_pendiente' => $this->faker->randomFloat(2, 0, 1000),
        ];
    }
}
