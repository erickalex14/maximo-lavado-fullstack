<?php

namespace Database\Factories;

use App\Models\Factura;
use App\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\Factory;

class FacturaFactory extends Factory
{
    protected $model = Factura::class;

    public function definition()
    {
        return [
            'numero_factura' => 'F-' . $this->faker->unique()->numerify('#####'),
            'cliente_id' => Cliente::inRandomOrder()->first()?->cliente_id ?? 1,
            'fecha' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'descripcion' => $this->faker->sentence(6),
            'total' => 0, // Se actualizará luego con los detalles
        ];
    }
}
