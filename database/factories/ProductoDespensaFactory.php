<?php

namespace Database\Factories;

use App\Models\ProductoDespensa;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductoDespensaFactory extends Factory
{
    protected $model = ProductoDespensa::class;

    public function definition()
    {
        return [
            'nombre' => $this->faker->word() . ' Despensa',
            'descripcion' => $this->faker->sentence(6),
            'precio_venta' => $this->faker->randomFloat(2, 1, 50),
            'stock' => $this->faker->numberBetween(0, 100),
            'activo' => $this->faker->boolean(90),
        ];
    }
}
