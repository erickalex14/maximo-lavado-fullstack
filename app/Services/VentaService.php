<?php

namespace App\Services;

use App\Contracts\VentaRepositoryInterface;
use App\Models\ProductoAutomotriz;
use App\Models\ProductoDespensa;
use App\Models\Cliente;
use App\Models\VentaProductoAutomotriz;
use App\Models\VentaProductoDespensa;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VentaService
{
    protected $ventaRepository;

    public function __construct(VentaRepositoryInterface $ventaRepository)
    {
        $this->ventaRepository = $ventaRepository;
    }

    public function getAllVentas(): Collection
    {
        return $this->ventaRepository->getAllVentas();
    }

    public function getVentasByClienteId(int $clienteId): Collection
    {
        return $this->ventaRepository->getVentasByClienteId($clienteId);
    }

    public function getVentasByFechaRange(string $fechaInicio, string $fechaFin): Collection
    {
        return $this->ventaRepository->getVentasByFechaRange($fechaInicio, $fechaFin);
    }

    public function getMetricas(): array
    {
        return $this->ventaRepository->getMetricas();
    }

    public function getProductosDisponibles(): array
    {
        $automotrices = ProductoAutomotriz::where('stock', '>', 0)
            ->get()
            ->map(function($producto) {
                return [
                    'id' => $producto->producto_automotriz_id,
                    'nombre' => $producto->nombre,
                    'precio_venta' => $producto->precio_venta,
                    'stock' => $producto->stock,
                    'tipo' => 'automotriz'
                ];
            });

        $despensa = ProductoDespensa::where('stock', '>', 0)
            ->get()
            ->map(function($producto) {
                return [
                    'id' => $producto->producto_despensa_id,
                    'nombre' => $producto->nombre,
                    'precio_venta' => $producto->precio_venta,
                    'stock' => $producto->stock,
                    'tipo' => 'despensa'
                ];
            });

        return [
            'automotrices' => $automotrices,
            'despensa' => $despensa
        ];
    }

    public function getClientes(): Collection
    {
        return Cliente::select('cliente_id as id', 'nombre', 'telefono')
            ->orderBy('nombre')
            ->get();
    }

    public function procesarVenta(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $ventasCreadas = [];
            $montoTotal = 0;

            foreach ($data['items'] as $item) {
                if ($item['tipo'] === 'automotriz') {
                    $resultado = $this->procesarVentaAutomotriz($item, $data['cliente_id'] ?? null);
                } else {
                    $resultado = $this->procesarVentaDespensa($item, $data['cliente_id'] ?? null);
                }

                $ventasCreadas[] = $resultado['venta'];
                $montoTotal += $resultado['total'];
            }

            return [
                'ventas' => $ventasCreadas,
                'monto_total' => $montoTotal
            ];
        });
    }

    private function procesarVentaAutomotriz(array $item, ?int $clienteId): array
    {
        $producto = ProductoAutomotriz::findOrFail($item['producto_id']);
        
        if ($producto->stock < $item['cantidad']) {
            throw new \Exception("Stock insuficiente para {$producto->nombre}. Disponible: {$producto->stock}");
        }

        $precioUnitario = $producto->precio_venta;
        $total = $precioUnitario * $item['cantidad'];

        $venta = $this->ventaRepository->createVentaAutomotriz([
            'producto_id' => $item['producto_id'],
            'cliente_id' => $clienteId,
            'cantidad' => $item['cantidad'],
            'precio_unitario' => $precioUnitario,
            'total' => $total,
            'fecha' => now()
        ]);

        // Actualizar stock
        $producto->decrement('stock', $item['cantidad']);

        return [
            'venta' => $venta,
            'total' => $total
        ];
    }

    private function procesarVentaDespensa(array $item, ?int $clienteId): array
    {
        $producto = ProductoDespensa::findOrFail($item['producto_id']);
        
        if ($producto->stock < $item['cantidad']) {
            throw new \Exception("Stock insuficiente para {$producto->nombre}. Disponible: {$producto->stock}");
        }

        $precioUnitario = $producto->precio_venta;
        $total = $precioUnitario * $item['cantidad'];

        $venta = $this->ventaRepository->createVentaDespensa([
            'producto_id' => $item['producto_id'],
            'cliente_id' => $clienteId,
            'cantidad' => $item['cantidad'],
            'precio_unitario' => $precioUnitario,
            'total' => $total,
            'fecha' => now()
        ]);

        // Actualizar stock
        $producto->decrement('stock', $item['cantidad']);

        return [
            'venta' => $venta,
            'total' => $total
        ];
    }

    public function findVentaById(int $id, string $tipo): ?object
    {
        if ($tipo === 'automotriz') {
            return $this->ventaRepository->findVentaAutomotrizById($id);
        } else {
            return $this->ventaRepository->findVentaDespensaById($id);
        }
    }

    public function updateVenta(int $id, string $tipo, array $data): ?object
    {
        if ($tipo === 'automotriz') {
            return $this->ventaRepository->updateVentaAutomotriz($id, $data);
        } else {
            return $this->ventaRepository->updateVentaDespensa($id, $data);
        }
    }

    public function deleteVenta(int $id, string $tipo): bool
    {
        if ($tipo === 'automotriz') {
            return $this->ventaRepository->deleteVentaAutomotriz($id);
        } else {
            return $this->ventaRepository->deleteVentaDespensa($id);
        }
    }
}
