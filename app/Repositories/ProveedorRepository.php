<?php

namespace App\Repositories;

use App\Contracts\ProveedorRepositoryInterface;
use App\Models\Proveedor;
use App\Models\PagoProveedor;
use App\Models\Egreso;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ProveedorRepository implements ProveedorRepositoryInterface
{
    protected $model;

    public function __construct(Proveedor $model)
    {
        $this->model = $model;
    }

    public function getAll(): Collection
    {
        return $this->model->all();
    }

    public function findById(int $id): ?Proveedor
    {
        return $this->model->where('proveedor_id', $id)->first();
    }

    public function create(array $data): Proveedor
    {
        // Asegurar que deuda_pendiente tenga un valor por defecto
        $data['deuda_pendiente'] = $data['deuda_pendiente'] ?? 0;
        
        return $this->model->create($data);
    }

    public function update(int $id, array $data): ?Proveedor
    {
        $proveedor = $this->findById($id);
        if ($proveedor) {
            $proveedor->update($data);
            return $proveedor->fresh();
        }
        return null;
    }

    public function delete(int $id): bool
    {
        $proveedor = $this->findById($id);
        if ($proveedor) {
            return $proveedor->delete();
        }
        return false;
    }

    public function getProveedoresWithPagos(): Collection
    {
        return $this->model->with('pagos')->get();
    }

    public function findByEmail(string $email): ?Proveedor
    {
        return $this->model->where('email', $email)->first();
    }

    public function getProveedoresConDeuda(): Collection
    {
        return $this->model->where('deuda_pendiente', '>', 0)->get();
    }

    public function updateDeuda(int $id, float $nuevaDeuda): ?Proveedor
    {
        $proveedor = $this->findById($id);
        if ($proveedor) {
            $proveedor->update(['deuda_pendiente' => $nuevaDeuda]);
            return $proveedor->fresh();
        }
        return null;
    }

    public function registrarPago(int $id, float $monto, string $descripcion = null): array
    {
        $proveedor = $this->findById($id);
        if (!$proveedor) {
            return ['success' => false, 'message' => 'Proveedor no encontrado'];
        }

        if ($proveedor->deuda_pendiente < $monto) {
            return ['success' => false, 'message' => 'El monto del pago excede la deuda pendiente'];
        }

        try {
            return DB::transaction(function () use ($proveedor, $id, $monto, $descripcion) {
                // Crear el registro de pago
                $pago = PagoProveedor::create([
                    'proveedor_id' => $id,
                    'monto' => $monto,
                    'descripcion' => $descripcion,
                    'fecha' => now(),
                ]);
                
                // Crear el egreso correspondiente
                $egreso = Egreso::create([
                    'fecha' => now()->format('Y-m-d'),
                    'tipo' => 'proveedor',
                    'referencia_id' => $pago->id_pago_proveedor,
                    'monto' => $monto,
                    'descripcion' => $descripcion ?? 'Pago a proveedor: ' . $proveedor->nombre,
                ]);
                
                // Reducir la deuda pendiente
                $nuevaDeuda = $proveedor->deuda_pendiente - $monto;
                $proveedor->update(['deuda_pendiente' => $nuevaDeuda]);
                
                return [
                    'success' => true,
                    'pago' => $pago,
                    'egreso' => $egreso,
                    'proveedor' => $proveedor->fresh()
                ];
            });
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error al registrar el pago: ' . $e->getMessage()];
        }
    }

    // =======================================================
    // MÉTODOS CONSOLIDADOS PARA GESTIÓN COMPLETA DE PAGOS
    // =======================================================

    /**
     * Obtener todos los pagos de todos los proveedores
     */
    public function getAllPagos(): Collection
    {
        return PagoProveedor::with('proveedor')->orderBy('fecha', 'desc')->get();
    }

    /**
     * Obtener un pago específico por ID
     */
    public function getPagoById(int $pagoId): ?object
    {
        return PagoProveedor::with('proveedor')->find($pagoId);
    }

    /**
     * Crear un pago con transacción completa
     * Registra el pago + actualiza deuda + registra egreso
     */
    public function createPago(array $data): array
    {
        try {
            return DB::transaction(function () use ($data) {
                $proveedor = $this->findById($data['proveedor_id']);
                if (!$proveedor) {
                    return ['success' => false, 'message' => 'Proveedor no encontrado'];
                }

                // Crear el registro de pago
                $pago = PagoProveedor::create([
                    'proveedor_id' => $data['proveedor_id'],
                    'monto' => $data['monto'],
                    'descripcion' => $data['descripcion'] ?? null,
                    'fecha' => $data['fecha'] ?? now(),
                ]);
                
                // Crear el egreso correspondiente
                $egreso = Egreso::create([
                    'fecha' => isset($data['fecha']) ? date('Y-m-d', strtotime($data['fecha'])) : now()->format('Y-m-d'),
                    'tipo' => 'proveedor',
                    'referencia_id' => $pago->id_pago_proveedor,
                    'monto' => $data['monto'],
                    'descripcion' => $data['descripcion'] ?? 'Pago a proveedor: ' . $proveedor->nombre,
                ]);
                
                // Reducir la deuda pendiente
                $nuevaDeuda = $proveedor->deuda_pendiente - $data['monto'];
                $proveedor->update(['deuda_pendiente' => max(0, $nuevaDeuda)]);
                
                return [
                    'success' => true,
                    'data' => $pago->load('proveedor'),
                    'egreso' => $egreso,
                    'proveedor' => $proveedor->fresh()
                ];
            });
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error al crear el pago: ' . $e->getMessage()];
        }
    }

    /**
     * Actualizar un pago específico
     */
    public function updatePago(int $pagoId, array $data): ?object
    {
        $pago = PagoProveedor::find($pagoId);
        if ($pago) {
            $pago->update($data);
            return $pago->fresh(['proveedor']);
        }
        return null;
    }

    /**
     * Eliminar un pago específico
     */
    public function deletePago(int $pagoId): bool
    {
        try {
            return DB::transaction(function () use ($pagoId) {
                $pago = PagoProveedor::find($pagoId);
                if (!$pago) {
                    return false;
                }

                // Restaurar la deuda al proveedor
                $proveedor = $this->findById($pago->proveedor_id);
                if ($proveedor) {
                    $nuevaDeuda = $proveedor->deuda_pendiente + $pago->monto;
                    $proveedor->update(['deuda_pendiente' => $nuevaDeuda]);
                }

                // Eliminar el egreso relacionado
                Egreso::where('tipo', 'proveedor')
                      ->where('referencia_id', $pago->id_pago_proveedor)
                      ->delete();

                // Eliminar el pago
                return $pago->delete();
            });
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Obtener pagos por proveedor
     */
    public function getPagosByProveedorId(int $proveedorId): Collection
    {
        return PagoProveedor::where('proveedor_id', $proveedorId)
                           ->orderBy('fecha', 'desc')
                           ->get();
    }

    /**
     * Obtener pagos por rango de fechas
     */
    public function getPagosByFechaRange(string $fechaInicio, string $fechaFin): Collection
    {
        return PagoProveedor::with('proveedor')
                           ->whereBetween('fecha', [$fechaInicio, $fechaFin])
                           ->orderBy('fecha', 'desc')
                           ->get();
    }

    /**
     * Obtener métricas de pagos
     */
    public function getMetricasPagos(array $params = []): array
    {
        $query = PagoProveedor::query();
        
        // Aplicar filtros si existen
        if (isset($params['fecha_inicio']) && isset($params['fecha_fin'])) {
            $query->whereBetween('fecha', [$params['fecha_inicio'], $params['fecha_fin']]);
        }
        
        if (isset($params['proveedor_id'])) {
            $query->where('proveedor_id', $params['proveedor_id']);
        }

        $totalPagos = $query->sum('monto');
        $cantidadPagos = $query->count();
        $promedioePago = $cantidadPagos > 0 ? $totalPagos / $cantidadPagos : 0;
        
        return [
            'total_pagos' => $totalPagos,
            'cantidad_pagos' => $cantidadPagos,
            'promedio_pago' => round($promedioePago, 2),
            'pagos_por_proveedor' => $this->getPagosPorProveedor($params),
            'total_deuda_pendiente' => $this->model->sum('deuda_pendiente'),
        ];
    }

    /**
     * Obtener estadísticas de pagos por proveedor
     */
    private function getPagosPorProveedor(array $params = []): Collection
    {
        $query = DB::table('pagos_proveedores')
                   ->join('proveedores', 'pagos_proveedores.proveedor_id', '=', 'proveedores.proveedor_id')
                   ->select(
                       'proveedores.nombre',
                       'proveedores.proveedor_id',
                       DB::raw('COUNT(*) as cantidad_pagos'),
                       DB::raw('SUM(pagos_proveedores.monto) as total_pagado'),
                       DB::raw('AVG(pagos_proveedores.monto) as promedio_pago')
                   )
                   ->groupBy('proveedores.proveedor_id', 'proveedores.nombre');

        // Aplicar filtros si existen
        if (isset($params['fecha_inicio']) && isset($params['fecha_fin'])) {
            $query->whereBetween('pagos_proveedores.fecha', [$params['fecha_inicio'], $params['fecha_fin']]);
        }

        return $query->get();
    }
}
