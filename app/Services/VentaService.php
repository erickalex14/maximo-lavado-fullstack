<?php

namespace App\Services;

use App\Contracts\VentaRepositoryInterface;
use App\Models\ProductoAutomotriz;
use App\Models\ProductoDespensa;
use App\Models\VentaProductoAutomotriz;
use App\Models\VentaProductoDespensa;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class VentaService
{
    protected $ventaRepository;

    public function __construct(VentaRepositoryInterface $ventaRepository)
    {
        $this->ventaRepository = $ventaRepository;
    }

    public function getAllVentas(): Collection
    {
        return $this->ventaRepository->getAllVentas();
    }

    public function getVentasByClienteId(int $clienteId): Collection
    {
        return $this->ventaRepository->getVentasByClienteId($clienteId);
    }

    public function getVentasByFechaRange(string $fechaInicio, string $fechaFin): Collection
    {
        return $this->ventaRepository->getVentasByFechaRange($fechaInicio, $fechaFin);
    }

    public function getMetricas(): array
    {
        return $this->ventaRepository->getMetricas();
    }

    public function getProductosDisponibles(): array
    {
        return $this->ventaRepository->getProductosDisponibles();
    }

    public function getClientes(): Collection
    {
        return $this->ventaRepository->getClientes();
    }

    public function procesarVenta(array $data): array
    {
        // Validaciones de negocio
        $this->validateVentaData($data);
        
        return $this->ventaRepository->procesarVentaCompleta($data);
    }

    // =======================================================
    // VALIDACIONES DE NEGOCIO
    // =======================================================

    private function validateVentaData(array $data): void
    {
        if (empty($data['items'])) {
            throw new \Exception('Debe incluir al menos un producto en la venta');
        }

        foreach ($data['items'] as $item) {
            if ($item['cantidad'] <= 0) {
                throw new \Exception('La cantidad debe ser mayor a 0');
            }
            
            if (!in_array($item['tipo'], ['automotriz', 'despensa'])) {
                throw new \Exception('Tipo de producto no válido');
            }
        }
    }

    public function findVentaById(int $id, string $tipo): ?object
    {
        if ($tipo === 'automotriz') {
            return $this->ventaRepository->findVentaAutomotrizById($id);
        } else {
            return $this->ventaRepository->findVentaDespensaById($id);
        }
    }

    public function updateVenta(int $id, string $tipo, array $data): ?object
    {
        if ($tipo === 'automotriz') {
            return $this->ventaRepository->updateVentaAutomotriz($id, $data);
        } else {
            return $this->ventaRepository->updateVentaDespensa($id, $data);
        }
    }

    public function deleteVenta(int $id, string $tipo): bool
    {
        if ($tipo === 'automotriz') {
            return $this->ventaRepository->deleteVentaAutomotriz($id);
        } else {
            return $this->ventaRepository->deleteVentaDespensa($id);
        }
    }

    // =======================================================
    // MÉTODOS CONSOLIDADOS PARA GESTIÓN ESPECÍFICA POR TIPO
    // =======================================================

    /**
     * Obtener todas las ventas de productos automotrices
     */
    public function getAllVentasAutomotrices(): Collection
    {
        return $this->ventaRepository->getAllVentasAutomotrices();
    }

    /**
     * Obtener ventas automotrices por rango de fechas
     */
    public function getVentasAutomotricesByFechaRange(string $fechaInicio, string $fechaFin): Collection
    {
        return $this->ventaRepository->getVentasAutomotricesByFechaRange($fechaInicio, $fechaFin);
    }

    /**
     * Crear una venta de producto automotriz con transacción
     */
    public function createVentaAutomotriz(array $data): object
    {
        return DB::transaction(function () use ($data) {
            $producto = ProductoAutomotriz::findOrFail($data['producto_id']);
            
            if ($producto->stock < $data['cantidad']) {
                throw new \Exception("Stock insuficiente para {$producto->nombre}. Disponible: {$producto->stock}");
            }

            // Calcular total si no se proporciona
            $total = $data['precio_unitario'] * $data['cantidad'];

            $ventaData = [
                'producto_id' => $data['producto_id'],
                'cliente_id' => $data['cliente_id'] ?? null,
                'cantidad' => $data['cantidad'],
                'precio_unitario' => $data['precio_unitario'],
                'total' => $total,
                'fecha' => $data['fecha'] ?? now()
            ];

            $venta = $this->ventaRepository->createVentaAutomotriz($ventaData);

            // Actualizar stock
            $producto->decrement('stock', $data['cantidad']);

            return $venta;
        });
    }

    /**
     * Obtener venta automotriz por ID
     */
    public function getVentaAutomotrizById(int $id): ?object
    {
        return $this->ventaRepository->findVentaAutomotrizById($id);
    }

    /**
     * Actualizar venta automotriz
     */
    public function updateVentaAutomotriz(int $id, array $data): ?object
    {
        return DB::transaction(function () use ($id, $data) {
            $venta = $this->ventaRepository->findVentaAutomotrizById($id);
            if (!$venta) {
                return null;
            }

            // Si cambia la cantidad, ajustar stock
            if (isset($data['cantidad']) && $data['cantidad'] != $venta->cantidad) {
                $producto = ProductoAutomotriz::findOrFail($venta->producto_id);
                $diferencia = $data['cantidad'] - $venta->cantidad;
                
                if ($diferencia > 0 && $producto->stock < $diferencia) {
                    throw new \Exception("Stock insuficiente. Disponible: {$producto->stock}");
                }
                
                $producto->decrement('stock', $diferencia);
            }

            // Recalcular total si es necesario
            if (isset($data['cantidad']) || isset($data['precio_unitario'])) {
                $cantidad = $data['cantidad'] ?? $venta->cantidad;
                $precioUnitario = $data['precio_unitario'] ?? $venta->precio_unitario;
                $data['total'] = $cantidad * $precioUnitario;
            }

            return $this->ventaRepository->updateVentaAutomotriz($id, $data);
        });
    }

    /**
     * Eliminar una venta de producto automotriz
     */
    public function deleteVentaAutomotriz(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $venta = $this->ventaRepository->findVentaAutomotrizById($id);
            if (!$venta) {
                return false;
            }

            // Restaurar stock
            $producto = ProductoAutomotriz::findOrFail($venta->producto_id);
            $producto->increment('stock', $venta->cantidad);

            return $this->ventaRepository->deleteVentaAutomotriz($id);
        });
    }

    /**
     * Restaurar venta automotriz eliminada lógicamente
     */
    public function restoreVentaAutomotriz(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $venta = VentaProductoAutomotriz::onlyTrashed()->findOrFail($id);
            
            // Verificar stock antes de restaurar
            $producto = ProductoAutomotriz::findOrFail($venta->producto_id);
            if ($producto->stock < $venta->cantidad) {
                throw new \Exception("Stock insuficiente para restaurar la venta. Disponible: {$producto->stock}, Requerido: {$venta->cantidad}");
            }

            $result = $this->ventaRepository->restoreVentaAutomotriz($id);
            
            if ($result) {
                // Decrementar stock nuevamente
                $producto->decrement('stock', $venta->cantidad);
            }
            
            return $result;
        });
    }

    /**
     * Obtener ventas automotrices eliminadas lógicamente
     */
    public function getTrashedVentasAutomotrices(): Collection
    {
        return $this->ventaRepository->getTrashedVentasAutomotrices();
    }

    /**
     * Obtener todas las ventas de productos de despensa
     */
    public function getAllVentasDespensa(): Collection
    {
        return $this->ventaRepository->getAllVentasDespensa();
    }

    /**
     * Obtener ventas de despensa por rango de fechas
     */
    public function getVentasDespensaByFechaRange(string $fechaInicio, string $fechaFin): Collection
    {
        return $this->ventaRepository->getVentasDespensaByFechaRange($fechaInicio, $fechaFin);
    }

    /**
     * Crear una venta de producto de despensa con transacción
     */
    public function createVentaDespensa(array $data): object
    {
        return DB::transaction(function () use ($data) {
            $producto = ProductoDespensa::findOrFail($data['producto_id']);
            
            if ($producto->stock < $data['cantidad']) {
                throw new \Exception("Stock insuficiente para {$producto->nombre}. Disponible: {$producto->stock}");
            }

            // Calcular total
            $total = $data['precio_unitario'] * $data['cantidad'];

            $ventaData = [
                'producto_id' => $data['producto_id'],
                'cliente_id' => $data['cliente_id'] ?? null,
                'cantidad' => $data['cantidad'],
                'precio_unitario' => $data['precio_unitario'],
                'total' => $total,
                'fecha' => $data['fecha'] ?? now()
            ];

            $venta = $this->ventaRepository->createVentaDespensa($ventaData);

            // Actualizar stock
            $producto->decrement('stock', $data['cantidad']);

            return $venta;
        });
    }

    /**
     * Obtener venta de despensa por ID
     */
    public function getVentaDespensaById(int $id): ?object
    {
        return $this->ventaRepository->findVentaDespensaById($id);
    }

    /**
     * Actualizar venta de despensa
     */
    public function updateVentaDespensa(int $id, array $data): ?object
    {
        return DB::transaction(function () use ($id, $data) {
            $venta = $this->ventaRepository->findVentaDespensaById($id);
            if (!$venta) {
                return null;
            }

            // Si cambia la cantidad, ajustar stock
            if (isset($data['cantidad']) && $data['cantidad'] != $venta->cantidad) {
                $producto = ProductoDespensa::findOrFail($venta->producto_id);
                $diferencia = $data['cantidad'] - $venta->cantidad;
                
                if ($diferencia > 0 && $producto->stock < $diferencia) {
                    throw new \Exception("Stock insuficiente. Disponible: {$producto->stock}");
                }
                
                $producto->decrement('stock', $diferencia);
            }

            // Recalcular total si es necesario
            if (isset($data['cantidad']) || isset($data['precio_unitario'])) {
                $cantidad = $data['cantidad'] ?? $venta->cantidad;
                $precioUnitario = $data['precio_unitario'] ?? $venta->precio_unitario;
                $data['total'] = $cantidad * $precioUnitario;
            }

            return $this->ventaRepository->updateVentaDespensa($id, $data);
        });
    }

    /**
     * Eliminar venta de despensa y restaurar stock
     */
    public function deleteVentaDespensa(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $venta = $this->ventaRepository->findVentaDespensaById($id);
            if (!$venta) {
                return false;
            }

            // Restaurar stock
            $producto = ProductoDespensa::findOrFail($venta->producto_id);
            $producto->increment('stock', $venta->cantidad);

            return $this->ventaRepository->deleteVentaDespensa($id);
        });
    }

    /**
     * Restaurar venta de despensa eliminada lógicamente
     */
    public function restoreVentaDespensa(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $venta = VentaProductoDespensa::onlyTrashed()->findOrFail($id);
            
            // Verificar stock antes de restaurar
            $producto = ProductoDespensa::findOrFail($venta->producto_id);
            if ($producto->stock < $venta->cantidad) {
                throw new \Exception("Stock insuficiente para restaurar la venta. Disponible: {$producto->stock}, Requerido: {$venta->cantidad}");
            }

            $result = $this->ventaRepository->restoreVentaDespensa($id);
            
            if ($result) {
                // Decrementar stock nuevamente
                $producto->decrement('stock', $venta->cantidad);
            }
            
            return $result;
        });
    }

    /**
     * Obtener ventas de despensa eliminadas lógicamente
     */
    public function getTrashedVentasDespensa(): Collection
    {
        return $this->ventaRepository->getTrashedVentasDespensa();
    }

    /**
     * Obtener métricas específicas de ventas automotrices
     */
    public function getMetricasAutomotrices(array $params = []): array
    {
        return $this->ventaRepository->getMetricasAutomotrices($params);
    }

    /**
     * Obtener métricas específicas de ventas de despensa
     */
    public function getMetricasDespensa(array $params = []): array
    {
        return $this->ventaRepository->getMetricasDespensa($params);
    }
}
