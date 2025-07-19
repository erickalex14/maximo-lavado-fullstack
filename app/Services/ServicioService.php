<?php

namespace App\Services;

use App\Contracts\ServicioRepositoryInterface;
use App\Contracts\TipoVehiculoRepositoryInterface;
use App\Models\Servicio;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ServicioService
{
    protected ServicioRepositoryInterface $servicioRepository;
    protected TipoVehiculoRepositoryInterface $tipoVehiculoRepository;

    public function __construct(
        ServicioRepositoryInterface $servicioRepository,
        TipoVehiculoRepositoryInterface $tipoVehiculoRepository
    ) {
        $this->servicioRepository = $servicioRepository;
        $this->tipoVehiculoRepository = $tipoVehiculoRepository;
    }

    /**
     * Obtener todos los servicios activos
     */
    public function getAllActivos(): Collection
    {
        return $this->servicioRepository->getActiveServices();
    }

    /**
     * Obtener servicio por ID
     */
    public function getById(int $id): ?Servicio
    {
        return $this->servicioRepository->findById($id);
    }

    /**
     * Crear nuevo servicio con precios por tipo de vehículo
     */
    public function crear(array $data): Servicio
    {
        // Validaciones de negocio
        $this->validarDatosServicio($data);

        try {
            return DB::transaction(function () use ($data) {
                // Extraer precios por tipo de vehículo
                $preciosPorTipo = $data['precios_por_tipo'] ?? [];
                unset($data['precios_por_tipo']);

                // Crear el servicio
                $servicio = $this->servicioRepository->create($data);

                // Crear precios por tipo de vehículo
                $this->crearPreciosPorTipo($servicio, $preciosPorTipo);
                
                Log::info('Servicio creado', [
                    'servicio_id' => $servicio->servicio_id,
                    'nombre' => $servicio->nombre,
                    'precios_configurados' => count($preciosPorTipo)
                ]);

                return $servicio->fresh(['precios.tipoVehiculo']);
            });
        } catch (\Exception $e) {
            Log::error('Error al crear servicio', [
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Actualizar servicio
     */
    public function actualizar(int $id, array $data): Servicio
    {
        $servicio = $this->servicioRepository->findById($id);
        
        if (!$servicio) {
            throw new \Exception("Servicio no encontrado con ID: {$id}");
        }

        // Validaciones de negocio
        $this->validarDatosServicio($data, $id);

        try {
            return DB::transaction(function () use ($id, $data, $servicio) {
                // Extraer precios por tipo de vehículo
                $preciosPorTipo = $data['precios_por_tipo'] ?? [];
                unset($data['precios_por_tipo']);

                // Actualizar el servicio
                $this->servicioRepository->update($id, $data);

                // Actualizar precios por tipo de vehículo si se proporcionan
                if (!empty($preciosPorTipo)) {
                    $this->actualizarPreciosPorTipo($servicio, $preciosPorTipo);
                }
                
                Log::info('Servicio actualizado', [
                    'servicio_id' => $id,
                    'cambios' => array_diff($data, $servicio->toArray())
                ]);

                return $this->servicioRepository->findById($id);
            });
        } catch (\Exception $e) {
            Log::error('Error al actualizar servicio', [
                'id' => $id,
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Eliminar servicio (soft delete)
     */
    public function eliminar(int $id): bool
    {
        $servicio = $this->servicioRepository->findById($id);
        
        if (!$servicio) {
            throw new \Exception("Servicio no encontrado con ID: {$id}");
        }

        // Validar que no tenga ventas asociadas
        if ($servicio->ventaDetalles()->exists()) {
            throw new \Exception("No se puede eliminar el servicio porque tiene ventas asociadas");
        }

        try {
            return DB::transaction(function () use ($id, $servicio) {
                $resultado = $this->servicioRepository->delete($id);
                
                Log::info('Servicio eliminado', [
                    'servicio_id' => $id,
                    'nombre' => $servicio->nombre
                ]);

                return $resultado;
            });
        } catch (\Exception $e) {
            Log::error('Error al eliminar servicio', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Restaurar servicio eliminado
     */
    public function restaurar(int $id): bool
    {
        try {
            return DB::transaction(function () use ($id) {
                $resultado = $this->servicioRepository->restore($id);
                
                Log::info('Servicio restaurado', [
                    'servicio_id' => $id
                ]);

                return $resultado;
            });
        } catch (\Exception $e) {
            Log::error('Error al restaurar servicio', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Toggle estado activo del servicio
     */
    public function toggleActivo(int $id): Servicio
    {
        $servicio = $this->servicioRepository->findById($id);
        
        if (!$servicio) {
            throw new \Exception("Servicio no encontrado con ID: {$id}");
        }

        try {
            return DB::transaction(function () use ($id, $servicio) {
                $servicioActualizado = $this->servicioRepository->toggleActive($id);
                
                Log::info('Estado del servicio cambiado', [
                    'servicio_id' => $id,
                    'nuevo_estado' => $servicioActualizado->activo ? 'activo' : 'inactivo'
                ]);

                return $servicioActualizado;
            });
        } catch (\Exception $e) {
            Log::error('Error al cambiar estado del servicio', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Obtener precio de servicio para un tipo de vehículo específico
     */
    public function getPrecioPorTipoVehiculo(int $servicioId, int $tipoVehiculoId): ?float
    {
        $servicio = $this->servicioRepository->findById($servicioId);
        
        if (!$servicio) {
            return null;
        }

        $precio = $servicio->precios()
            ->where('tipo_vehiculo_id', $tipoVehiculoId)
            ->first();

        return $precio ? $precio->precio : null;
    }

    /**
     * Actualizar precio específico de servicio para un tipo de vehículo
     */
    public function actualizarPrecio(int $servicioId, int $tipoVehiculoId, float $precio): bool
    {
        if ($precio <= 0) {
            throw new \Exception("El precio debe ser mayor a 0");
        }

        try {
            return DB::transaction(function () use ($servicioId, $tipoVehiculoId, $precio) {
                $resultado = $this->servicioRepository->updatePrecio($servicioId, $tipoVehiculoId, $precio);
                
                Log::info('Precio de servicio actualizado', [
                    'servicio_id' => $servicioId,
                    'tipo_vehiculo_id' => $tipoVehiculoId,
                    'nuevo_precio' => $precio
                ]);

                return $resultado;
            });
        } catch (\Exception $e) {
            Log::error('Error al actualizar precio de servicio', [
                'servicio_id' => $servicioId,
                'tipo_vehiculo_id' => $tipoVehiculoId,
                'precio' => $precio,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Obtener servicios por tipo de vehículo
     */
    public function getPorTipoVehiculo(int $tipoVehiculoId): Collection
    {
        return $this->servicioRepository->getByTipoVehiculo($tipoVehiculoId);
    }

    /**
     * Buscar servicios por nombre
     */
    public function buscarPorNombre(string $nombre): Collection
    {
        return $this->servicioRepository->search($nombre);
    }

    /**
     * Obtener servicios con estadísticas
     */
    public function getConEstadisticas(): Collection
    {
        return $this->servicioRepository->getAll()->map(function ($servicio) {
            return [
                'servicio' => $servicio,
                'total_ventas' => $servicio->ventaDetalles()->count(),
                'precio_minimo' => $servicio->precios()->min('precio') ?? 0,
                'precio_maximo' => $servicio->precios()->max('precio') ?? 0,
                'tipos_vehiculo_configurados' => $servicio->precios()->count(),
            ];
        });
    }

    /**
     * Verificar si un servicio puede ser eliminado
     */
    public function puedeSerEliminado(int $id): array
    {
        $servicio = $this->servicioRepository->findById($id);
        
        if (!$servicio) {
            return [
                'puede_eliminar' => false,
                'motivo' => 'Servicio no encontrado'
            ];
        }

        $ventasAsociadas = $servicio->ventaDetalles()->count();

        if ($ventasAsociadas > 0) {
            return [
                'puede_eliminar' => false,
                'motivo' => "Tiene {$ventasAsociadas} venta(s) asociada(s)"
            ];
        }

        return [
            'puede_eliminar' => true,
            'motivo' => null
        ];
    }

    /**
     * Obtener estadísticas generales de servicios
     */
    public function getEstadisticas(): array
    {
        $stats = $this->servicioRepository->getStats();
        
        return [
            'total_servicios' => $stats['total'],
            'servicios_activos' => $stats['activos'],
            'servicios_inactivos' => $stats['inactivos'],
            'servicios_eliminados' => $stats['eliminados'],
            'precio_promedio' => DB::table('servicio_precios')->avg('precio') ?? 0,
            'servicio_mas_vendido' => $this->getServicioMasVendido(),
        ];
    }

    /**
     * Obtener el servicio más vendido
     */
    private function getServicioMasVendido(): ?array
    {
        $resultado = DB::table('venta_detalles')
            ->where('vendible_type', 'App\Models\Servicio')
            ->select('vendible_id', DB::raw('SUM(cantidad) as total_vendido'))
            ->groupBy('vendible_id')
            ->orderByDesc('total_vendido')
            ->first();

        if (!$resultado) {
            return null;
        }

        $servicio = $this->servicioRepository->findById($resultado->vendible_id);
        
        return [
            'servicio' => $servicio,
            'total_vendido' => $resultado->total_vendido
        ];
    }

    /**
     * Crear precios por tipo de vehículo
     */
    private function crearPreciosPorTipo(Servicio $servicio, array $preciosPorTipo): void
    {
        foreach ($preciosPorTipo as $tipoVehiculoId => $precio) {
            if ($precio > 0) {
                $servicio->precios()->create([
                    'tipo_vehiculo_id' => $tipoVehiculoId,
                    'precio' => $precio
                ]);
            }
        }
    }

    /**
     * Actualizar precios por tipo de vehículo
     */
    private function actualizarPreciosPorTipo(Servicio $servicio, array $preciosPorTipo): void
    {
        foreach ($preciosPorTipo as $tipoVehiculoId => $precio) {
            if ($precio > 0) {
                $this->servicioRepository->updatePrecio($servicio->servicio_id, $tipoVehiculoId, $precio);
            }
        }
    }

    /**
     * Validar datos del servicio
     */
    private function validarDatosServicio(array $data, ?int $excludeId = null): void
    {
        // Validar que el nombre no esté vacío
        if (empty($data['nombre'])) {
            throw new \Exception("El nombre del servicio es obligatorio");
        }

        // Validar que el nombre no exista ya
        if ($this->servicioRepository->existsByNombre($data['nombre'], $excludeId)) {
            throw new \Exception("Ya existe un servicio con el nombre: {$data['nombre']}");
        }

        // Validar longitud del nombre
        if (strlen($data['nombre']) > 100) {
            throw new \Exception("El nombre del servicio no puede tener más de 100 caracteres");
        }

        // Validar descripción si se proporciona
        if (isset($data['descripcion']) && strlen($data['descripcion']) > 500) {
            throw new \Exception("La descripción no puede tener más de 500 caracteres");
        }

        // Validar precios si se proporcionan
        if (isset($data['precios_por_tipo'])) {
            foreach ($data['precios_por_tipo'] as $tipoVehiculoId => $precio) {
                if ($precio <= 0) {
                    throw new \Exception("Todos los precios deben ser mayores a 0");
                }
                
                if (!$this->tipoVehiculoRepository->findById($tipoVehiculoId)) {
                    throw new \Exception("Tipo de vehículo no encontrado con ID: {$tipoVehiculoId}");
                }
            }
        }
    }
}
