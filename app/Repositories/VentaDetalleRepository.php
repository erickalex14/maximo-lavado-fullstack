<?php

namespace App\Repositories;

use App\Contracts\VentaDetalleRepositoryInterface;
use App\Models\VentaDetalle;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class VentaDetalleRepository implements VentaDetalleRepositoryInterface
{
    /**
     * Obtener todos los detalles de venta
     */
    public function getAll(): Collection
    {
        return VentaDetalle::all();
    }

    /**
     * Obtener todos incluyendo eliminados (soft deletes)
     */
    public function getAllWithTrashed(): Collection
    {
        return VentaDetalle::withTrashed()->get();
    }

    /**
     * Obtener solo los eliminados (soft deletes)
     */
    public function getOnlyTrashed(): Collection
    {
        return VentaDetalle::onlyTrashed()->get();
    }

    /**
     * Buscar detalle de venta por ID
     */
    public function findById(int $id): ?VentaDetalle
    {
        return VentaDetalle::find($id);
    }

    /**
     * Buscar detalle de venta por ID incluyendo eliminados
     */
    public function findByIdWithTrashed(int $id): ?VentaDetalle
    {
        return VentaDetalle::withTrashed()->find($id);
    }

    /**
     * Crear nuevo detalle de venta
     */
    public function create(array $data): VentaDetalle
    {
        return DB::transaction(function () use ($data) {
            return VentaDetalle::create($data);
        });
    }

    /**
     * Actualizar detalle de venta
     */
    public function update(int $id, array $data): ?VentaDetalle
    {
        return DB::transaction(function () use ($id, $data) {
            $detalle = VentaDetalle::find($id);
            if ($detalle) {
                $detalle->update($data);
                return $detalle->fresh();
            }
            return null;
        });
    }

    /**
     * Eliminar detalle de venta (soft delete)
     */
    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $detalle = VentaDetalle::find($id);
            return $detalle ? $detalle->delete() : false;
        });
    }

    /**
     * Restaurar detalle de venta eliminado
     */
    public function restore(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $detalle = VentaDetalle::onlyTrashed()->find($id);
            return $detalle ? $detalle->restore() : false;
        });
    }

    /**
     * Eliminar permanentemente
     */
    public function forceDelete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $detalle = VentaDetalle::withTrashed()->find($id);
            return $detalle ? $detalle->forceDelete() : false;
        });
    }

    /**
     * Obtener detalles por venta ID
     */
    public function getByVentaId(int $ventaId): Collection
    {
        return VentaDetalle::where('venta_id', $ventaId)->get();
    }

    /**
     * Obtener detalles de productos por venta ID
     */
    public function getProductosByVentaId(int $ventaId): Collection
    {
        return VentaDetalle::where('venta_id', $ventaId)
                          ->where('vendible_type', 'LIKE', '%Producto%')
                          ->with('vendible')
                          ->get();
    }

    /**
     * Obtener detalles de servicios por venta ID
     */
    public function getServiciosByVentaId(int $ventaId): Collection
    {
        return VentaDetalle::where('venta_id', $ventaId)
                          ->where('vendible_type', 'App\Models\Servicio')
                          ->with('vendible')
                          ->get();
    }

    /**
     * Crear múltiples detalles de venta
     */
    public function createMultiple(array $detallesData): Collection
    {
        return DB::transaction(function () use ($detallesData) {
            $detalles = collect();
            
            foreach ($detallesData as $detalleData) {
                $detalle = VentaDetalle::create($detalleData);
                $detalles->push($detalle);
            }
            
            return $detalles;
        });
    }

    /**
     * Actualizar múltiples detalles de venta
     */
    public function updateMultiple(int $ventaId, array $detallesData): Collection
    {
        return DB::transaction(function () use ($ventaId, $detallesData) {
            // Eliminar detalles existentes
            VentaDetalle::where('venta_id', $ventaId)->delete();
            
            // Crear nuevos detalles
            return $this->createMultiple($detallesData);
        });
    }

    /**
     * Eliminar todos los detalles de una venta
     */
    public function deleteByVentaId(int $ventaId): bool
    {
        return DB::transaction(function () use ($ventaId) {
            return VentaDetalle::where('venta_id', $ventaId)->delete();
        });
    }

    /**
     * Obtener el total de una venta por detalles
     */
    public function getTotalByVentaId(int $ventaId): float
    {
        return VentaDetalle::where('venta_id', $ventaId)
                          ->sum(DB::raw('precio_unitario * cantidad'));
    }

    /**
     * Obtener productos más vendidos
     */
    public function getProductosMasVendidos(int $limit = 10): Collection
    {
        return VentaDetalle::select('vendible_type', 'vendible_id', DB::raw('SUM(cantidad) as total_vendido'))
                          ->where('vendible_type', 'LIKE', '%Producto%')
                          ->groupBy('vendible_type', 'vendible_id')
                          ->orderByDesc('total_vendido')
                          ->limit($limit)
                          ->with('vendible')
                          ->get();
    }

    /**
     * Obtener servicios más vendidos
     */
    public function getServiciosMasVendidos(int $limit = 10): Collection
    {
        return VentaDetalle::select('vendible_type', 'vendible_id', DB::raw('SUM(cantidad) as total_vendido'))
                          ->where('vendible_type', 'App\Models\Servicio')
                          ->groupBy('vendible_type', 'vendible_id')
                          ->orderByDesc('total_vendido')
                          ->limit($limit)
                          ->with('vendible')
                          ->get();
    }

    /**
     * Obtener ventas por rango de fechas
     */
    public function getByDateRange(string $fechaInicio, string $fechaFin): Collection
    {
        return VentaDetalle::whereHas('venta', function ($query) use ($fechaInicio, $fechaFin) {
            $query->whereBetween('fecha', [$fechaInicio, $fechaFin]);
        })->with(['venta', 'vendible'])->get();
    }

    /**
     * Obtener estadísticas de detalles de venta
     */
    public function getStats(): array
    {
        return [
            'total_detalles' => VentaDetalle::count(),
            'productos_vendidos' => VentaDetalle::where('vendible_type', 'LIKE', '%Producto%')->count(),
            'servicios_vendidos' => VentaDetalle::where('vendible_type', 'App\Models\Servicio')->count(),
            'eliminados' => VentaDetalle::onlyTrashed()->count(),
        ];
    }
}
