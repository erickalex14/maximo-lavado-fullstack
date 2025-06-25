<?php

namespace Database\Factories;

use App\Models\ProductoAutomotriz;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductoAutomotrizFactory extends Factory
{
    protected $model = ProductoAutomotriz::class;

    public function definition()
    {
        return [
            'codigo' => $this->faker->unique()->bothify('AUTO-####'),
            'nombre' => $this->faker->word() . ' Auto',
            'descripcion' => $this->faker->sentence(6),
            'precio_venta' => $this->faker->randomFloat(2, 2, 100),
            'stock' => $this->faker->numberBetween(0, 100),
            'activo' => $this->faker->boolean(90),
        ];
    }
}
