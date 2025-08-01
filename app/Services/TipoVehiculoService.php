<?php

namespace App\Services;

use App\Contracts\TipoVehiculoRepositoryInterface;
use App\Models\TipoVehiculo;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TipoVehiculoService
{
    protected TipoVehiculoRepositoryInterface $tipoVehiculoRepository;

    public function __construct(TipoVehiculoRepositoryInterface $tipoVehiculoRepository)
    {
        $this->tipoVehiculoRepository = $tipoVehiculoRepository;
    }

    /**
     * Obtener todos los tipos de vehículo activos
     */
    public function getAllActivos(): Collection
    {
        return $this->tipoVehiculoRepository->getAll();
    }

    /**
     * Obtener todos los tipos de vehículo (alias para compatibilidad)
     */
    public function getAll(): Collection
    {
        return $this->getAllActivos();
    }

    /**
     * Obtener tipos de vehículo paginados
     */
    public function getAllPaginated(int $perPage = 15, array $filters = []): \Illuminate\Pagination\LengthAwarePaginator
    {
        return $this->tipoVehiculoRepository->getPaginated($perPage, $filters);
    }

    /**
     * Obtener tipo de vehículo por ID
     */
    public function getById(int $id): ?TipoVehiculo
    {
        return $this->tipoVehiculoRepository->findById($id);
    }

    /**
     * Crear nuevo tipo de vehículo
     */
    public function crear(array $data): TipoVehiculo
    {
        // Validaciones de negocio
        $this->validarDatosTipoVehiculo($data);

        try {
            return DB::transaction(function () use ($data) {
                $tipoVehiculo = $this->tipoVehiculoRepository->create($data);
                
                Log::info('Tipo de vehículo creado', [
                    'tipo_vehiculo_id' => $tipoVehiculo->tipo_vehiculo_id,
                    'nombre' => $tipoVehiculo->nombre
                ]);

                return $tipoVehiculo;
            });
        } catch (\Exception $e) {
            Log::error('Error al crear tipo de vehículo', [
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Actualizar tipo de vehículo
     */
    public function actualizar(int $id, array $data): TipoVehiculo
    {
        $tipoVehiculo = $this->tipoVehiculoRepository->findById($id);
        
        if (!$tipoVehiculo) {
            throw new \Exception("Tipo de vehículo no encontrado con ID: {$id}");
        }

        // Validaciones de negocio
        $this->validarDatosTipoVehiculo($data, $id);

        try {
            return DB::transaction(function () use ($id, $data, $tipoVehiculo) {
                $this->tipoVehiculoRepository->update($id, $data);
                $tipoVehiculoActualizado = $this->tipoVehiculoRepository->findById($id);
                
                Log::info('Tipo de vehículo actualizado', [
                    'tipo_vehiculo_id' => $id,
                    'cambios' => array_diff($data, $tipoVehiculo->toArray())
                ]);

                return $tipoVehiculoActualizado;
            });
        } catch (\Exception $e) {
            Log::error('Error al actualizar tipo de vehículo', [
                'id' => $id,
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Eliminar tipo de vehículo (soft delete)
     */
    public function eliminar(int $id): bool
    {
        $tipoVehiculo = $this->tipoVehiculoRepository->findById($id);
        
        if (!$tipoVehiculo) {
            throw new \Exception("Tipo de vehículo no encontrado con ID: {$id}");
        }

        // Validar que no haya vehículos asociados
        if ($tipoVehiculo->vehiculos()->exists()) {
            throw new \Exception("No se puede eliminar el tipo de vehículo porque tiene vehículos asociados");
        }

        // Validar que no haya servicios con precios asociados
        if ($tipoVehiculo->servicios()->exists()) {
            throw new \Exception("No se puede eliminar el tipo de vehículo porque tiene servicios con precios asociados");
        }

        try {
            return DB::transaction(function () use ($id, $tipoVehiculo) {
                $resultado = $this->tipoVehiculoRepository->delete($id);
                
                Log::info('Tipo de vehículo eliminado', [
                    'tipo_vehiculo_id' => $id,
                    'nombre' => $tipoVehiculo->nombre
                ]);

                return $resultado;
            });
        } catch (\Exception $e) {
            Log::error('Error al eliminar tipo de vehículo', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Restaurar tipo de vehículo eliminado
     */
    public function restaurar(int $id): bool
    {
        try {
            return DB::transaction(function () use ($id) {
                $resultado = $this->tipoVehiculoRepository->restore($id);
                
                Log::info('Tipo de vehículo restaurado', [
                    'tipo_vehiculo_id' => $id
                ]);

                return $resultado;
            });
        } catch (\Exception $e) {
            Log::error('Error al restaurar tipo de vehículo', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Obtener tipos de vehículo con estadísticas
     */
    public function getConEstadisticas(): Collection
    {
        return $this->tipoVehiculoRepository->getAll()->map(function ($tipo) {
            return [
                'tipo_vehiculo' => $tipo,
                'total_vehiculos' => $tipo->vehiculos()->count(),
                'servicios_disponibles' => $tipo->servicios()->count(),
                'vehiculos_activos' => $tipo->vehiculos()->count(), // Todos están activos por soft delete
            ];
        });
    }

    /**
     * Buscar tipos de vehículo por nombre
     */
    public function buscarPorNombre(string $nombre): Collection
    {
        return $this->tipoVehiculoRepository->search($nombre);
    }

    /**
     * Verificar si un tipo de vehículo puede ser eliminado
     */
    public function puedeSerEliminado(int $id): array
    {
        $tipoVehiculo = $this->tipoVehiculoRepository->findById($id);
        
        if (!$tipoVehiculo) {
            return [
                'puede_eliminar' => false,
                'motivo' => 'Tipo de vehículo no encontrado'
            ];
        }

        $vehiculosAsociados = $tipoVehiculo->vehiculos()->count();
        $serviciosAsociados = $tipoVehiculo->servicios()->count();

        if ($vehiculosAsociados > 0) {
            return [
                'puede_eliminar' => false,
                'motivo' => "Tiene {$vehiculosAsociados} vehículo(s) asociado(s)"
            ];
        }

        if ($serviciosAsociados > 0) {
            return [
                'puede_eliminar' => false,
                'motivo' => "Tiene {$serviciosAsociados} servicio(s) con precios asociado(s)"
            ];
        }

        return [
            'puede_eliminar' => true,
            'motivo' => null
        ];
    }

    /**
     * Validar datos del tipo de vehículo
     */
    private function validarDatosTipoVehiculo(array $data, ?int $excludeId = null): void
    {
        // Validar que el nombre no esté vacío
        if (empty($data['nombre'])) {
            throw new \Exception("El nombre del tipo de vehículo es obligatorio");
        }

        // Validar que el nombre no exista ya
        if ($this->tipoVehiculoRepository->existsByNombre($data['nombre'], $excludeId)) {
            throw new \Exception("Ya existe un tipo de vehículo con el nombre: {$data['nombre']}");
        }

        // Validar longitud del nombre
        if (strlen($data['nombre']) > 100) {
            throw new \Exception("El nombre del tipo de vehículo no puede tener más de 100 caracteres");
        }

        // Validar descripción si se proporciona
        if (isset($data['descripcion']) && strlen($data['descripcion']) > 500) {
            throw new \Exception("La descripción no puede tener más de 500 caracteres");
        }
    }

    /**
     * Obtener estadísticas generales de tipos de vehículo
     */
    public function getEstadisticas(): array
    {
        return [
            'total_tipos' => $this->tipoVehiculoRepository->getAll()->count(),
            'total_vehiculos' => DB::table('vehiculos')->count(),
            'tipos_con_vehiculos' => $this->tipoVehiculoRepository->getAll()
                ->filter(function ($tipo) {
                    return $tipo->vehiculos()->count() > 0;
                })->count(),
            'tipos_con_servicios' => $this->tipoVehiculoRepository->getAll()
                ->filter(function ($tipo) {
                    return $tipo->servicios()->count() > 0;
                })->count(),
        ];
    }

    /**
     * Alternar estado activo/inactivo
     */
    public function toggleActivo(int $id): TipoVehiculo
    {
        $tipoVehiculo = $this->tipoVehiculoRepository->findById($id);
        
        if (!$tipoVehiculo) {
            throw new \Exception("Tipo de vehículo no encontrado con ID: {$id}");
        }

        try {
            return DB::transaction(function () use ($id, $tipoVehiculo) {
                $nuevoEstado = !$tipoVehiculo->activo;
                $this->tipoVehiculoRepository->update($id, ['activo' => $nuevoEstado]);
                
                Log::info('Estado de tipo de vehículo cambiado', [
                    'tipo_vehiculo_id' => $id,
                    'estado_anterior' => $tipoVehiculo->activo,
                    'estado_nuevo' => $nuevoEstado
                ]);

                return $this->tipoVehiculoRepository->findById($id);
            });
        } catch (\Exception $e) {
            Log::error('Error al cambiar estado del tipo de vehículo', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Obtener tipos de vehículo eliminados
     */
    public function getTrashed(): Collection
    {
        return $this->tipoVehiculoRepository->getOnlyTrashed();
    }
}
