<?php

namespace App\Services;

use App\Contracts\VehiculoRepositoryInterface;
use App\Contracts\TipoVehiculoRepositoryInterface;
use App\Models\Vehiculo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class VehiculoService
{
    public function __construct(
        protected VehiculoRepositoryInterface $vehiculoRepository,
        protected TipoVehiculoRepositoryInterface $tipoVehiculoRepository
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
            'matricula' => $vehiculo->matricula,
            'tipo_vehiculo_id' => $vehiculo->tipo_vehiculo_id
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

    /**
     * Obtener tipos de vehículo activos disponibles
     */
    public function getTiposVehiculoDisponibles(): Collection
    {
        return $this->tipoVehiculoRepository->getActivos();
    }

    /**
     * Obtener vehículos por tipo
     */
    public function getVehiculosPorTipo(int $tipoVehiculoId): Collection
    {
        return $this->vehiculoRepository->getByTipoVehiculo($tipoVehiculoId);
    }

    protected function validateBusinessRules(array $data, ?int $excludeId = null): void
    {
        // Verificar matrícula única (si se proporciona)
        if (isset($data['matricula']) && !empty($data['matricula'])) {
            if ($this->vehiculoRepository->existsByPlaca($data['matricula'], $excludeId)) {
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

        // ⚡ NUEVO: Validar tipo de vehículo dinámico
        if (isset($data['tipo_vehiculo_id'])) {
            $tipoVehiculo = $this->tipoVehiculoRepository->findById($data['tipo_vehiculo_id']);
            if (!$tipoVehiculo) {
                throw new \Exception('El tipo de vehículo seleccionado no existe');
            }
            if (!$tipoVehiculo->activo) {
                throw new \Exception('El tipo de vehículo seleccionado no está activo');
            }
        }

        // ⚠️ DEPRECADO: Mantener compatibilidad con tipos legacy temporalmente
        if (isset($data['tipo'])) {
            Log::warning('Uso de campo "tipo" legacy detectado', [
                'tipo_legacy' => $data['tipo'],
                'mensaje' => 'Migrar a tipo_vehiculo_id'
            ]);
            
            $tiposValidos = ['moto', 'camioneta', 'auto_pequeno', 'auto_mediano'];
            if (!in_array($data['tipo'], $tiposValidos)) {
                throw new \Exception('Tipo de vehículo legacy no válido');
            }
        }
    }

    protected function validateDeletion(Vehiculo $vehiculo): void
    {
        // ⚡ NUEVO: Verificar si tiene ventas asociadas (sistema unificado)
        $ventasCount = \App\Models\VentaDetalle::whereHas('vendible', function($query) use ($vehiculo) {
            $query->where('vendible_type', 'App\Models\Servicio')
                  ->whereHas('servicios', function($subQuery) use ($vehiculo) {
                      // Verificar servicios que requieren este tipo de vehículo
                      $subQuery->where('tipo_vehiculo_id', $vehiculo->tipo_vehiculo_id);
                  });
        })->count();
        
        if ($ventasCount > 0) {
            throw new \Exception('No se puede eliminar el vehículo porque tiene ventas de servicios registradas');
        }

        // ⚠️ LEGACY: Verificar lavados legacy (mantener temporalmente)
        $lavadosCount = \App\Models\Lavado::where('vehiculo_id', $vehiculo->vehiculo_id)->count();
        if ($lavadosCount > 0) {
            throw new \Exception('No se puede eliminar el vehículo porque tiene lavados legacy registrados');
        }

        // ⚠️ LEGACY: Verificar facturas legacy (mantener temporalmente)
        $facturasCount = \App\Models\FacturaDetalle::whereHas('lavado', function($query) use ($vehiculo) {
            $query->where('vehiculo_id', $vehiculo->vehiculo_id);
        })->count();
        
        if ($facturasCount > 0) {
            throw new \Exception('No se puede eliminar el vehículo porque está asociado a facturas legacy');
        }
    }
}
