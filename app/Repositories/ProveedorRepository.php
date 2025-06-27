<?php

namespace App\Repositories;

use App\Contracts\ProveedorRepositoryInterface;
use App\Models\Proveedor;
use App\Models\PagoProveedor;
use Illuminate\Database\Eloquent\Collection;

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

    public function registrarPago(int $id, float $monto, string $descripcion = null): bool
    {
        $proveedor = $this->findById($id);
        if ($proveedor && $proveedor->deuda_pendiente >= $monto) {
            // Crear el registro de pago
            PagoProveedor::create([
                'proveedor_id' => $id,
                'monto' => $monto,
                'descripcion' => $descripcion,
                'fecha' => now(),
            ]);
            
            // Reducir la deuda pendiente
            $nuevaDeuda = $proveedor->deuda_pendiente - $monto;
            $proveedor->update(['deuda_pendiente' => $nuevaDeuda]);
            
            return true;
        }
        return false;
    }
}
