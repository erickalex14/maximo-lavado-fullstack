<?php

namespace App\Services;

use App\Contracts\LavadoRepositoryInterface;
use App\Models\Lavado;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
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

    public function getAllLavados(): Collection
    {
        return $this->lavadoRepository->getAll();
    }

    public function getLavadoById(int $id): ?Lavado
    {
        return $this->lavadoRepository->findById($id);
    }

    public function createLavado(array $data): Lavado
    {
        try {
            DB::beginTransaction();

            // Validaciones de negocio
            $this->validateBusinessRules($data);

            $lavado = $this->lavadoRepository->create($data);

            DB::commit();

            Log::info('Lavado creado exitosamente', [
                'lavado_id' => $lavado->id,
                'cliente_id' => $lavado->cliente_id,
                'precio' => $lavado->precio
            ]);

            return $lavado;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear lavado', [
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function updateLavado(int $id, array $data): Lavado
    {
        try {
            DB::beginTransaction();

            $lavado = $this->lavadoRepository->findById($id);
            if (!$lavado) {
                throw new \Exception('Lavado no encontrado');
            }

            // Validaciones de negocio
            $this->validateBusinessRules($data, $id);

            $lavadoActualizado = $this->lavadoRepository->update($id, $data);

            DB::commit();

            Log::info('Lavado actualizado exitosamente', [
                'lavado_id' => $id,
                'cambios' => array_diff_assoc($data, $lavado->toArray())
            ]);

            return $lavadoActualizado;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar lavado', [
                'lavado_id' => $id,
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function deleteLavado(int $id): bool
    {
        try {
            DB::beginTransaction();

            $lavado = $this->lavadoRepository->findById($id);
            if (!$lavado) {
                throw new \Exception('Lavado no encontrado');
            }

            // Verificar si se puede eliminar
            $this->validateDeletion($lavado);

            $result = $this->lavadoRepository->delete($id);

            DB::commit();

            Log::info('Lavado eliminado exitosamente', [
                'lavado_id' => $id,
                'cliente_id' => $lavado->cliente_id
            ]);

            return $result;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar lavado', [
                'lavado_id' => $id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function getLavadosByCliente(int $clienteId): Collection
    {
        return $this->lavadoRepository->getByCliente($clienteId);
    }

    public function getLavadosByEmpleado(int $empleadoId): Collection
    {
        return $this->lavadoRepository->getByEmpleado($empleadoId);
    }

    public function getLavadosByDateRange(string $fechaInicio, string $fechaFin): Collection
    {
        return $this->lavadoRepository->getByDateRange($fechaInicio, $fechaFin);
    }

    public function getEstadisticas(): array
    {
        return $this->lavadoRepository->getStats();
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

    protected function validateBusinessRules(array $data, ?int $excludeId = null): void
    {
        // Verificar que el vehículo pertenezca al cliente
        if (isset($data['cliente_id']) && isset($data['vehiculo_id'])) {
            $vehiculo = \App\Models\Vehiculo::find($data['vehiculo_id']);
            if ($vehiculo && $vehiculo->cliente_id != $data['cliente_id']) {
                throw new \Exception('El vehículo no pertenece al cliente seleccionado');
            }
        }

        // Verificar que el empleado esté activo
        if (isset($data['empleado_id'])) {
            $empleado = \App\Models\Empleado::find($data['empleado_id']);
            if ($empleado && !$empleado->activo) {
                throw new \Exception('El empleado seleccionado no está activo');
            }
        }

        // Validar precio según tipo de lavado
        if (isset($data['tipo_lavado']) && isset($data['precio'])) {
            $this->validatePrecioByTipo($data['tipo_lavado'], $data['precio']);
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
