<?php

namespace App\Repositories;

use App\Contracts\PagoProveedorRepositoryInterface;
use App\Models\PagoProveedor;
use App\Models\Egreso;
use App\Models\Proveedor;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class PagoProveedorRepository implements PagoProveedorRepositoryInterface
{
    public function getAll(): Collection
    {
        return PagoProveedor::with('proveedor')
            ->orderBy('fecha', 'desc')
            ->get();
    }
    
    public function findById(int $id): ?PagoProveedor
    {
        return PagoProveedor::with('proveedor')->find($id);
    }
    
    public function create(array $data): array
    {
        try {
            return DB::transaction(function () use ($data) {
                // Verificar que el proveedor existe
                $proveedor = Proveedor::where('proveedor_id', $data['proveedor_id'])->first();
                if (!$proveedor) {
                    return ['success' => false, 'message' => 'Proveedor no encontrado'];
                }
                
                $data['fecha'] = $data['fecha'] ?? now();
                
                // Crear el pago
                $pago = PagoProveedor::create($data);
                
                // Crear el egreso correspondiente
                $egreso = Egreso::create([
                    'fecha' => now()->format('Y-m-d'),
                    'tipo' => 'proveedor',
                    'referencia_id' => $pago->id_pago_proveedor,
                    'monto' => $pago->monto,
                    'descripcion' => $data['descripcion'] ?? 'Pago a proveedor: ' . $proveedor->nombre,
                ]);
                
                // Reducir la deuda pendiente del proveedor
                if ($proveedor->deuda_pendiente >= $pago->monto) {
                    $proveedor->decrement('deuda_pendiente', $pago->monto);
                }
                
                return [
                    'success' => true,
                    'pago' => $pago->load('proveedor'),
                    'egreso' => $egreso,
                    'proveedor' => $proveedor->fresh()
                ];
            });
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error al crear el pago: ' . $e->getMessage()];
        }
    }
    
    public function update(int $id, array $data): ?PagoProveedor
    {
        $pagoProveedor = PagoProveedor::find($id);
        
        if (!$pagoProveedor) {
            return null;
        }
        
        $pagoProveedor->update($data);
        
        return $pagoProveedor->fresh(['proveedor']);
    }
    
    public function delete(int $id): bool
    {
        $pagoProveedor = PagoProveedor::find($id);
        
        if (!$pagoProveedor) {
            return false;
        }
        
        return $pagoProveedor->delete();
    }
    
    public function getByProveedorId(int $proveedorId): Collection
    {
        return PagoProveedor::with('proveedor')
            ->where('proveedor_id', $proveedorId)
            ->orderBy('fecha', 'desc')
            ->get();
    }
    
    public function getByFechaRange(string $fechaInicio, string $fechaFin): Collection
    {
        return PagoProveedor::with('proveedor')
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->orderBy('fecha', 'desc')
            ->get();
    }
    
    public function getTotalPagosByPeriodo(string $fechaInicio, string $fechaFin): float
    {
        return PagoProveedor::whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->sum('monto');
    }
    
    public function getPagosByMes(int $año, int $mes): Collection
    {
        return PagoProveedor::with('proveedor')
            ->whereYear('fecha', $año)
            ->whereMonth('fecha', $mes)
            ->orderBy('fecha', 'desc')
            ->get();
    }
}
