<?php

namespace Database\Factories;

use App\Models\Vehiculo;
use App\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\Factory;

class VehiculoFactory extends Factory
{
    protected $model = Vehiculo::class;

    public function definition()
    {
        return [
            'cliente_id' => \App\Models\Cliente::inRandomOrder()->first()?->cliente_id ?? 1,
            'tipo' => $this->faker->randomElement(['moto', 'camioneta', 'auto_pequeno', 'auto_mediano']),
            'matricula' => $this->faker->optional()->bothify('???-####'),
            'descripcion' => $this->faker->sentence(4),
        ];
    }
}
