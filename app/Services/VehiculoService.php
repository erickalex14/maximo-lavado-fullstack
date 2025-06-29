<?php

namespace App\Services;

use App\Contracts\VehiculoRepositoryInterface;
use App\Models\Vehiculo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class VehiculoService
{
    public function __construct(
        protected VehiculoRepositoryInterface $vehiculoRepository
    ) {}

    public function getVehiculosPaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->vehiculoRepository->getAllPaginated($perPage, $filters);
    }

    public function getAllVehiculos(): Collection
    {
        return $this->vehiculoRepository->getAll();
    }

    public function getVehiculoById(int $id): ?Vehiculo
    {
        return $this->vehiculoRepository->findById($id);
    }

    public function createVehiculo(array $data): Vehiculo
    {
        // Validaciones de negocio
        $this->validateBusinessRules($data);

        $vehiculo = $this->vehiculoRepository->create($data);

        Log::info('Vehículo creado exitosamente', [
            'vehiculo_id' => $vehiculo->vehiculo_id,
            'cliente_id' => $vehiculo->cliente_id,
            'matricula' => $vehiculo->matricula
        ]);

        return $vehiculo;
    }

    public function updateVehiculo(int $id, array $data): Vehiculo
    {
        $vehiculo = $this->vehiculoRepository->findById($id);
        if (!$vehiculo) {
            throw new \Exception('Vehículo no encontrado');
        }

        // Validaciones de negocio
        $this->validateBusinessRules($data, $id);

        $vehiculoActualizado = $this->vehiculoRepository->update($id, $data);

        Log::info('Vehículo actualizado exitosamente', [
            'vehiculo_id' => $id
        ]);

        return $vehiculoActualizado;
    }

    public function deleteVehiculo(int $id): bool
    {
        $vehiculo = $this->vehiculoRepository->findById($id);
        if (!$vehiculo) {
            throw new \Exception('Vehículo no encontrado');
        }

        // Verificar si se puede eliminar
        $this->validateDeletion($vehiculo);

        $result = $this->vehiculoRepository->delete($id);

        Log::info('Vehículo eliminado exitosamente', [
            'vehiculo_id' => $id,
            'matricula' => $vehiculo->matricula
        ]);

        return $result;
    }

    /**
     * Restaurar vehículo eliminado lógicamente
     */
    public function restoreVehiculo(int $id): bool
    {
        $result = $this->vehiculoRepository->restore($id);

        if ($result) {
            Log::info('Vehículo restaurado exitosamente', [
                'vehiculo_id' => $id
            ]);
        }

        return $result;
    }

    /**
     * Obtener vehículos eliminados lógicamente
     */
    public function getTrashedVehiculos(): Collection
    {
        return $this->vehiculoRepository->getTrashed();
    }

    public function getVehiculosByCliente(int $clienteId): Collection
    {
        return $this->vehiculoRepository->getByCliente($clienteId);
    }

    public function getEstadisticas(): array
    {
        return $this->vehiculoRepository->getStats();
    }

    protected function validateBusinessRules(array $data, ?int $excludeId = null): void
    {
        // Verificar matrícula única (si se proporciona)
        if (isset($data['matricula']) && !empty($data['matricula'])) {
            if ($this->vehiculoRepository->existsByMatricula($data['matricula'], $excludeId)) {
                throw new \Exception('Ya existe un vehículo con esta matrícula');
            }
        }

        // Verificar que el cliente existe y está activo
        if (isset($data['cliente_id'])) {
            $cliente = \App\Models\Cliente::find($data['cliente_id']);
            if (!$cliente) {
                throw new \Exception('El cliente seleccionado no existe');
            }
        }

        // Validar tipo de vehículo
        $tiposValidos = ['moto', 'camioneta', 'auto_pequeno', 'auto_mediano'];
        if (isset($data['tipo']) && !in_array($data['tipo'], $tiposValidos)) {
            throw new \Exception('Tipo de vehículo no válido');
        }
    }

    protected function validateDeletion(Vehiculo $vehiculo): void
    {
        // Verificar si tiene lavados asociados
        $lavadosCount = \App\Models\Lavado::where('vehiculo_id', $vehiculo->vehiculo_id)->count();
        if ($lavadosCount > 0) {
            throw new \Exception('No se puede eliminar el vehículo porque tiene lavados registrados');
        }

        // Verificar si está en facturas
        $facturasCount = \App\Models\FacturaDetalle::whereHas('lavado', function($query) use ($vehiculo) {
            $query->where('vehiculo_id', $vehiculo->vehiculo_id);
        })->count();
        
        if ($facturasCount > 0) {
            throw new \Exception('No se puede eliminar el vehículo porque está asociado a facturas');
        }
    }
}
