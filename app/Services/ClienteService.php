<?php

namespace App\Services;

use App\Contracts\ClienteRepositoryInterface;
use App\Models\Cliente;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClienteService
{
    public function __construct(
        protected ClienteRepositoryInterface $clienteRepository
    ) {}

    /**
     * Obtener clientes paginados con filtros
     */
    public function getClientesPaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->clienteRepository->getAllPaginated($perPage, $filters);
    }

    /**
     * Obtener todos los clientes para select/dropdown
     */
    public function getAllClientes(): Collection
    {
        return $this->clienteRepository->getAll();
    }

    /**
     * Obtener cliente por ID
     */
    public function getClienteById(int $id): ?Cliente
    {
        return $this->clienteRepository->findById($id);
    }

    /**
     * Crear nuevo cliente
     */
    public function createCliente(array $data): Cliente
    {
        try {
            DB::beginTransaction();

            // Validaciones de negocio adicionales
            $this->validateBusinessRules($data);

            $cliente = $this->clienteRepository->create($data);

            DB::commit();

            Log::info('Cliente creado exitosamente', [
                'cliente_id' => $cliente->id,
                'nombre' => $cliente->nombre
            ]);

            return $cliente;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear cliente', [
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Actualizar cliente
     */
    public function updateCliente(int $id, array $data): Cliente
    {
        try {
            DB::beginTransaction();

            // Verificar que el cliente existe
            $cliente = $this->clienteRepository->findById($id);
            if (!$cliente) {
                throw new \Exception('Cliente no encontrado');
            }

            // Validaciones de negocio adicionales
            $this->validateBusinessRules($data, $id);

            $clienteActualizado = $this->clienteRepository->update($id, $data);

            DB::commit();

            Log::info('Cliente actualizado exitosamente', [
                'cliente_id' => $id,
                'cambios' => array_diff_assoc($data, $cliente->toArray())
            ]);

            return $clienteActualizado;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar cliente', [
                'cliente_id' => $id,
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Eliminar cliente
     */
    public function deleteCliente(int $id): bool
    {
        try {
            DB::beginTransaction();

            $cliente = $this->clienteRepository->findById($id);
            if (!$cliente) {
                throw new \Exception('Cliente no encontrado');
            }

            // Verificar si el cliente tiene relaciones que impidan su eliminación
            $this->validateDeletion($cliente);

            $result = $this->clienteRepository->delete($id);

            DB::commit();

            Log::info('Cliente eliminado exitosamente', [
                'cliente_id' => $id,
                'nombre' => $cliente->nombre
            ]);

            return $result;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar cliente', [
                'cliente_id' => $id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Buscar clientes
     */
    public function searchClientes(string $term): Collection
    {
        return $this->clienteRepository->search($term);
    }

    /**
     * Obtener estadísticas de clientes
     */
    public function getEstadisticas(): array
    {
        return $this->clienteRepository->getStats();
    }

    /**
     * Activar/Desactivar cliente
     */
    public function toggleActivo(int $id): Cliente
    {
        $cliente = $this->getClienteById($id);
        if (!$cliente) {
            throw new \Exception('Cliente no encontrado');
        }

        return $this->updateCliente($id, ['activo' => !$cliente->activo]);
    }

    /**
     * Validaciones de reglas de negocio
     */
    protected function validateBusinessRules(array $data, ?int $excludeId = null): void
    {
        // Verificar email único
        if (isset($data['email']) && $this->clienteRepository->existsByEmail($data['email'], $excludeId)) {
            throw new \Exception('Ya existe un cliente con este email');
        }

        // Verificar teléfono único
        if (isset($data['telefono']) && $this->clienteRepository->existsByTelefono($data['telefono'], $excludeId)) {
            throw new \Exception('Ya existe un cliente con este teléfono');
        }

        // Otras validaciones de negocio específicas
        // Por ejemplo: validar formato de teléfono, dominios de email permitidos, etc.
    }

    /**
     * Validar que se puede eliminar el cliente
     */
    protected function validateDeletion(Cliente $cliente): void
    {
        // Verificar si tiene lavados asociados
        if ($cliente->lavados()->count() > 0) {
            throw new \Exception('No se puede eliminar el cliente porque tiene lavados registrados');
        }

        // Verificar si tiene vehículos asociados
        if ($cliente->vehiculos()->count() > 0) {
            throw new \Exception('No se puede eliminar el cliente porque tiene vehículos registrados');
        }

        // Agregar más validaciones según las reglas de negocio
    }
}
