<?php

namespace Database\Factories;

use App\Models\FacturaDetalle;
use App\Models\Factura;
use App\Models\ProductoAutomotriz;
use App\Models\ProductoDespensa;
use App\Models\Lavado;
use Illuminate\Database\Eloquent\Factories\Factory;

class FacturaDetalleFactory extends Factory
{
    protected $model = FacturaDetalle::class;

    public function definition()
    {
        // Aleatoriamente asignar producto automotriz, despensa o lavado
        $tipo = $this->faker->randomElement(['automotriz', 'despensa', 'lavado']);
        $producto_id = null;
        $lavado_id = null;
        if ($tipo === 'automotriz') {
            $producto_id = ProductoAutomotriz::inRandomOrder()->first()?->producto_automotriz_id;
        } elseif ($tipo === 'despensa') {
            $producto_id = ProductoDespensa::inRandomOrder()->first()?->producto_despensa_id;
        } elseif ($tipo === 'lavado') {
            $lavado_id = Lavado::inRandomOrder()->first()?->lavado_id;
        }
        $cantidad = $this->faker->numberBetween(1, 3);
        $precio_unitario = $this->faker->randomFloat(2, 5, 50);
        $subtotal = $cantidad * $precio_unitario;
        return [
            'factura_id' => Factura::inRandomOrder()->first()?->factura_id ?? 1,
            'producto_id' => $producto_id,
            'lavado_id' => $lavado_id,
            'cantidad' => $cantidad,
            'precio_unitario' => $precio_unitario,
            'subtotal' => $subtotal,
        ];
    }
}
