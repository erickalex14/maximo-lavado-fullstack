<?php

namespace Database\Factories;

use App\Models\Lavado;
use App\Models\Vehiculo;
use App\Models\Empleado;
use Illuminate\Database\Eloquent\Factories\Factory;

class LavadoFactory extends Factory
{
    protected $model = Lavado::class;

    public function definition()
    {
        return [
            'vehiculo_id' => \App\Models\Vehiculo::inRandomOrder()->first()?->vehiculo_id ?? 1,
            'empleado_id' => \App\Models\Empleado::inRandomOrder()->first()?->empleado_id ?? 1,
            'fecha' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'tipo_lavado' => $this->faker->randomElement(['completo', 'solo_fuera', 'solo_por_dentro']),
            'precio' => $this->faker->randomFloat(2, 5, 50),
            'pulverizado' => $this->faker->boolean(30),
        ];
    }
}
