<?php

namespace App\Services;

use App\Contracts\LavadoRepositoryInterface;
use App\Models\Lavado;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class LavadoService
{
    public function __construct(
        protected LavadoRepositoryInterface $lavadoRepository
    ) {}

    public function getLavadosPaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->lavadoRepository->getAllPaginated($perPage, $filters);
    }

    public function getAllLavados(array $filters = []): Collection
    {
        return $this->lavadoRepository->getAll($filters);
    }

    public function getLavadoById(int $id): ?Lavado
    {
        return $this->lavadoRepository->findById($id);
    }

    public function createLavado(array $data): array
    {
        try {
            // Validaciones de negocio
            $validation = $this->validateBusinessRules($data);
            if (!$validation['valid']) {
                return ['success' => false, 'message' => $validation['message']];
            }

            $result = $this->lavadoRepository->create($data);
            
            if ($result['success']) {
                Log::info('Lavado, ingreso y factura creados exitosamente', [
                    'lavado_id' => $result['lavado']->lavado_id,
                    'ingreso_id' => $result['ingreso']->ingreso_id,
                    'factura_id' => $result['factura']->factura_id,
                    'numero_factura' => $result['factura']->numero_factura,
                    'vehiculo_id' => $result['lavado']->vehiculo_id,
                    'precio' => $result['lavado']->precio
                ]);
                
                return [
                    'success' => true,
                    'message' => 'Lavado creado, registrado como ingreso y facturado correctamente',
                    'data' => [
                        'lavado' => $result['lavado'],
                        'ingreso' => $result['ingreso'],
                        'factura' => $result['factura']
                    ]
                ];
            }

            return $result;

        } catch (\Exception $e) {
            Log::error('Error al crear lavado', [
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            return ['success' => false, 'message' => 'Error interno: ' . $e->getMessage()];
        }
    }

    public function updateLavado(int $id, array $data): Lavado
    {
        // Validaciones de negocio
        $validation = $this->validateBusinessRules($data, $id);
        if (!$validation['valid']) {
            throw new \Exception($validation['message']);
        }

        $lavadoActualizado = $this->lavadoRepository->update($id, $data);
        
        Log::info('Lavado actualizado exitosamente', [
            'lavado_id' => $id
        ]);

        return $lavadoActualizado;
    }

    public function deleteLavado(int $id): bool
    {
        $lavado = $this->lavadoRepository->findById($id);
        if (!$lavado) {
            throw new \Exception('Lavado no encontrado');
        }

        // Verificar si se puede eliminar
        $this->validateDeletion($lavado);

        $result = $this->lavadoRepository->delete($id);

        Log::info('Lavado eliminado exitosamente', [
            'lavado_id' => $id,
            'cliente_id' => $lavado->vehiculo->cliente_id ?? null
        ]);

        return $result;
    }

    /**
     * Restaurar lavado eliminado lógicamente
     */
    public function restoreLavado(int $id): bool
    {
        $result = $this->lavadoRepository->restore($id);

        if ($result) {
            Log::info('Lavado restaurado exitosamente', [
                'lavado_id' => $id
            ]);
        }

        return $result;
    }

    /**
     * Obtener lavados eliminados lógicamente
     */
    public function getTrashedLavados(): Collection
    {
        return $this->lavadoRepository->getTrashed();
    }

    public function getLavadosByCliente(int $clienteId): Collection
    {
        return $this->lavadoRepository->getByCliente($clienteId);
    }

    public function getLavadosByEmpleado(int $empleadoId, array $filters = []): Collection
    {
        return $this->lavadoRepository->getByEmpleado($empleadoId, $filters);
    }

    public function getLavadosByVehiculo(int $vehiculoId, array $filters = []): Collection
    {
        return $this->lavadoRepository->getByVehiculo($vehiculoId, $filters);
    }

    public function getLavadosByDay(string $fecha, array $filters = []): Collection
    {
        return $this->lavadoRepository->getByDay($fecha, $filters);
    }

    public function getLavadosByWeek(string $fecha, array $filters = []): Collection
    {
        return $this->lavadoRepository->getByWeek($fecha, $filters);
    }

    public function getLavadosByMonth(int $anio, int $mes, array $filters = []): Collection
    {
        return $this->lavadoRepository->getByMonth($anio, $mes, $filters);
    }

    public function getLavadosByYear(int $anio, array $filters = []): Collection
    {
        return $this->lavadoRepository->getByYear($anio, $filters);
    }

    public function getLavadosByDateRange(string $fechaInicio, string $fechaFin): Collection
    {
        return $this->lavadoRepository->getByDateRange($fechaInicio, $fechaFin);
    }

    public function getEstadisticas(array $filters = []): array
    {
        return $this->lavadoRepository->getStats($filters);
    }

    public function getLavadosRecientes(int $limit = 10): Collection
    {
        return $this->lavadoRepository->getRecientes($limit);
    }

    public function cambiarEstado(int $id, string $estado): Lavado
    {
        $lavado = $this->getLavadoById($id);
        if (!$lavado) {
            throw new \Exception('Lavado no encontrado');
        }

        $estadosValidos = ['pendiente', 'en_proceso', 'completado', 'cancelado'];
        if (!in_array($estado, $estadosValidos)) {
            throw new \Exception('Estado no válido');
        }

        return $this->updateLavado($id, ['estado' => $estado]);
    }

    protected function validateBusinessRules(array $data, ?int $excludeId = null): array
    {
        try {
            // Verificar que el vehículo existe
            if (isset($data['vehiculo_id'])) {
                $vehiculo = \App\Models\Vehiculo::find($data['vehiculo_id']);
                if (!$vehiculo) {
                    return ['valid' => false, 'message' => 'El vehículo seleccionado no existe'];
                }
            }

            // Verificar que el empleado existe
            if (isset($data['empleado_id'])) {
                $empleado = \App\Models\Empleado::find($data['empleado_id']);
                if (!$empleado) {
                    return ['valid' => false, 'message' => 'El empleado seleccionado no existe'];
                }
            }

            // Validar precio
            if (isset($data['precio']) && $data['precio'] <= 0) {
                return ['valid' => false, 'message' => 'El precio debe ser mayor a 0'];
            }

            return ['valid' => true];
        } catch (\Exception $e) {
            return ['valid' => false, 'message' => $e->getMessage()];
        }
    }

    protected function validateDeletion(Lavado $lavado): void
    {
        // Solo se pueden eliminar lavados pendientes o cancelados
        if (in_array($lavado->estado, ['completado', 'en_proceso'])) {
            throw new \Exception('No se puede eliminar un lavado completado o en proceso');
        }
    }

    protected function validatePrecioByTipo(string $tipo, float $precio): void
    {
        $preciosMinimos = [
            'basico' => 50,
            'completo' => 100,
            'premium' => 150,
            'encerado' => 200
        ];

        if (isset($preciosMinimos[$tipo]) && $precio < $preciosMinimos[$tipo]) {
            throw new \Exception("El precio mínimo para lavado {$tipo} es \${$preciosMinimos[$tipo]}");
        }
    }
}
