<?php

namespace App\Repositories;

use App\Contracts\VentaRepositoryInterface;
use App\Models\VentaProductoAutomotriz;
use App\Models\VentaProductoDespensa;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

class VentaRepository implements VentaRepositoryInterface
{
    public function getVentasAutomotrices(): Collection
    {
        return VentaProductoAutomotriz::with(['productoAutomotriz', 'cliente'])
            ->orderBy('fecha', 'desc')
            ->get();
    }

    public function getVentasDespensa(): Collection
    {
        return VentaProductoDespensa::with(['productoDespensa', 'cliente'])
            ->orderBy('fecha', 'desc')
            ->get();
    }

    public function getAllVentas(): Collection
    {
        $ventasAutomotrices = $this->getVentasAutomotrices()->map(function($venta) {
            return [
                'id' => $venta->venta_producto_automotriz_id,
                'fecha' => $venta->fecha,
                'cliente' => $venta->cliente ? $venta->cliente->nombre : 'Cliente General',
                'producto' => $venta->productoAutomotriz->nombre,
                'cantidad' => $venta->cantidad,
                'precio_unitario' => $venta->precio_unitario,
                'total' => $venta->total,
                'tipo' => 'automotriz'
            ];
        });

        $ventasDespensa = $this->getVentasDespensa()->map(function($venta) {
            return [
                'id' => $venta->venta_producto_despensa_id,
                'fecha' => $venta->fecha,
                'cliente' => $venta->cliente ? $venta->cliente->nombre : 'Cliente General',
                'producto' => $venta->productoDespensa->nombre,
                'cantidad' => $venta->cantidad,
                'precio_unitario' => $venta->precio_unitario,
                'total' => $venta->total,
                'tipo' => 'despensa'
            ];
        });

        return $ventasAutomotrices->merge($ventasDespensa)
            ->sortByDesc('fecha')
            ->values();
    }

    public function createVentaAutomotriz(array $data): VentaProductoAutomotriz
    {
        return VentaProductoAutomotriz::create($data);
    }

    public function createVentaDespensa(array $data): VentaProductoDespensa
    {
        return VentaProductoDespensa::create($data);
    }

    public function findVentaAutomotrizById(int $id): ?VentaProductoAutomotriz
    {
        return VentaProductoAutomotriz::with(['productoAutomotriz', 'cliente'])->find($id);
    }

    public function findVentaDespensaById(int $id): ?VentaProductoDespensa
    {
        return VentaProductoDespensa::with(['productoDespensa', 'cliente'])->find($id);
    }

    public function updateVentaAutomotriz(int $id, array $data): ?VentaProductoAutomotriz
    {
        $venta = VentaProductoAutomotriz::find($id);
        if ($venta) {
            $venta->update($data);
            return $venta->fresh(['productoAutomotriz', 'cliente']);
        }
        return null;
    }

    public function updateVentaDespensa(int $id, array $data): ?VentaProductoDespensa
    {
        $venta = VentaProductoDespensa::find($id);
        if ($venta) {
            $venta->update($data);
            return $venta->fresh(['productoDespensa', 'cliente']);
        }
        return null;
    }

    public function deleteVentaAutomotriz(int $id): bool
    {
        $venta = VentaProductoAutomotriz::find($id);
        return $venta ? $venta->delete() : false;
    }

    public function deleteVentaDespensa(int $id): bool
    {
        $venta = VentaProductoDespensa::find($id);
        return $venta ? $venta->delete() : false;
    }

    public function getVentasByClienteId(int $clienteId): Collection
    {
        $ventasAutomotrices = VentaProductoAutomotriz::with(['productoAutomotriz', 'cliente'])
            ->where('cliente_id', $clienteId)
            ->get()
            ->map(function($venta) {
                return [
                    'id' => $venta->venta_producto_automotriz_id,
                    'fecha' => $venta->fecha,
                    'cliente' => $venta->cliente->nombre,
                    'producto' => $venta->productoAutomotriz->nombre,
                    'cantidad' => $venta->cantidad,
                    'precio_unitario' => $venta->precio_unitario,
                    'total' => $venta->total,
                    'tipo' => 'automotriz'
                ];
            });

        $ventasDespensa = VentaProductoDespensa::with(['productoDespensa', 'cliente'])
            ->where('cliente_id', $clienteId)
            ->get()
            ->map(function($venta) {
                return [
                    'id' => $venta->venta_producto_despensa_id,
                    'fecha' => $venta->fecha,
                    'cliente' => $venta->cliente->nombre,
                    'producto' => $venta->productoDespensa->nombre,
                    'cantidad' => $venta->cantidad,
                    'precio_unitario' => $venta->precio_unitario,
                    'total' => $venta->total,
                    'tipo' => 'despensa'
                ];
            });

        return $ventasAutomotrices->merge($ventasDespensa)
            ->sortByDesc('fecha')
            ->values();
    }

    public function getVentasByFechaRange(string $fechaInicio, string $fechaFin): Collection
    {
        $ventasAutomotrices = VentaProductoAutomotriz::with(['productoAutomotriz', 'cliente'])
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->get()
            ->map(function($venta) {
                return [
                    'id' => $venta->venta_producto_automotriz_id,
                    'fecha' => $venta->fecha,
                    'cliente' => $venta->cliente ? $venta->cliente->nombre : 'Cliente General',
                    'producto' => $venta->productoAutomotriz->nombre,
                    'cantidad' => $venta->cantidad,
                    'precio_unitario' => $venta->precio_unitario,
                    'total' => $venta->total,
                    'tipo' => 'automotriz'
                ];
            });

        $ventasDespensa = VentaProductoDespensa::with(['productoDespensa', 'cliente'])
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->get()
            ->map(function($venta) {
                return [
                    'id' => $venta->venta_producto_despensa_id,
                    'fecha' => $venta->fecha,
                    'cliente' => $venta->cliente ? $venta->cliente->nombre : 'Cliente General',
                    'producto' => $venta->productoDespensa->nombre,
                    'cantidad' => $venta->cantidad,
                    'precio_unitario' => $venta->precio_unitario,
                    'total' => $venta->total,
                    'tipo' => 'despensa'
                ];
            });

        return $ventasAutomotrices->merge($ventasDespensa)
            ->sortByDesc('fecha')
            ->values();
    }

    public function getMetricas(): array
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();

        $ventasHoy = VentaProductoAutomotriz::whereDate('created_at', $today)->count() + 
                    VentaProductoDespensa::whereDate('created_at', $today)->count();

        $ventasMes = VentaProductoAutomotriz::where('created_at', '>=', $thisMonth)->count() + 
                    VentaProductoDespensa::where('created_at', '>=', $thisMonth)->count();

        $ingresoHoy = VentaProductoAutomotriz::whereDate('created_at', $today)->sum('total') + 
                     VentaProductoDespensa::whereDate('created_at', $today)->sum('total');

        $ingresoMes = VentaProductoAutomotriz::where('created_at', '>=', $thisMonth)->sum('total') + 
                     VentaProductoDespensa::where('created_at', '>=', $thisMonth)->sum('total');

        return [
            'ventasHoy' => $ventasHoy,
            'ventasMes' => $ventasMes,
            'ingresoHoy' => $ingresoHoy,
            'ingresoMes' => $ingresoMes
        ];
    }
}
