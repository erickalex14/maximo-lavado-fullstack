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
}
