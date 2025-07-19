<?php

namespace App\Services;

use App\Contracts\VentaRepositoryInterface;
use App\Contracts\VentaDetalleRepositoryInterface;
use App\Contracts\IngresoRepositoryInterface;
use App\Contracts\ProductoAutomotrizRepositoryInterface;
use App\Contracts\ProductoDespensaRepositoryInterface;
use App\Models\Venta;
use App\Models\VentaDetalle;
use App\Services\FacturaElectronicaService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VentaService
{
    protected VentaRepositoryInterface $ventaRepository;
    protected VentaDetalleRepositoryInterface $detalleRepository;
    protected IngresoRepositoryInterface $ingresoRepository;
    protected ProductoAutomotrizRepositoryInterface $productoAutomotrizRepository;
    protected ProductoDespensaRepositoryInterface $productoDespensaRepository;
    protected FacturaElectronicaService $facturaElectronicaService;

    public function __construct(
        VentaRepositoryInterface $ventaRepository,
        VentaDetalleRepositoryInterface $detalleRepository,
        IngresoRepositoryInterface $ingresoRepository,
        ProductoAutomotrizRepositoryInterface $productoAutomotrizRepository,
        ProductoDespensaRepositoryInterface $productoDespensaRepository,
        FacturaElectronicaService $facturaElectronicaService
    ) {
        $this->ventaRepository = $ventaRepository;
        $this->detalleRepository = $detalleRepository;
        $this->ingresoRepository = $ingresoRepository;
        $this->productoAutomotrizRepository = $productoAutomotrizRepository;
        $this->productoDespensaRepository = $productoDespensaRepository;
        $this->facturaElectronicaService = $facturaElectronicaService;
    }

    /**
     * ⚡ MÉTODO CRÍTICO: Crear venta completa con flujo automático
     * Venta → Factura Electrónica → Ingreso → Actualización Stock
     */
    public function crearVentaCompleta(array $datosVenta, array $detallesVenta): Venta
    {
        try {
            return DB::transaction(function () use ($datosVenta, $detallesVenta) {
                Log::info('Iniciando creación de venta completa', [
                    'cliente_id' => $datosVenta['cliente_id'],
                    'total_items' => count($detallesVenta)
                ]);

                // PASO 0: VALIDACIONES PREVIAS ✅
                $this->validarVentaCompleta($datosVenta, $detallesVenta);

                // PASO 1: VALIDAR STOCK DISPONIBLE ⚡ CRÍTICO
                $this->validarStockDisponible($detallesVenta);

                // PASO 2: CALCULAR TOTALES
                $totales = $this->calcularTotales($detallesVenta);
                $datosVenta = array_merge($datosVenta, $totales);

                // PASO 3: CREAR VENTA
                $venta = $this->ventaRepository->create($datosVenta);

                // PASO 4: CREAR DETALLES DE VENTA
                $this->crearDetallesVenta($venta, $detallesVenta);

                // PASO 5: ACTUALIZAR STOCK DE PRODUCTOS ⚡ CRÍTICO
                $this->actualizarStockProductos($detallesVenta);

                // PASO 6: GENERAR FACTURA ELECTRÓNICA AUTOMÁTICA ⚡ CRÍTICO
                $facturaElectronica = $this->facturaElectronicaService->generarDesdeVenta($venta);

                // PASO 7: CREAR INGRESO FINANCIERO AUTOMÁTICO ⚡ CRÍTICO
                $this->crearIngresoAutomatico($venta);

                // PASO 8: PROCESAR FACTURA CON SRI (opcional, puede ser asíncrono)
                try {
                    $this->facturaElectronicaService->procesarConSRI($facturaElectronica->factura_electronica_id);
                } catch (\Exception $e) {
                    // No falla la transacción si el SRI tiene problemas, se puede reenviar después
                    Log::warning('Error al procesar factura con SRI (se puede reenviar)', [
                        'venta_id' => $venta->venta_id,
                        'factura_id' => $facturaElectronica->factura_electronica_id,
                        'error' => $e->getMessage()
                    ]);
                }

                Log::info('Venta completa creada exitosamente', [
                    'venta_id' => $venta->venta_id,
                    'factura_id' => $facturaElectronica->factura_electronica_id,
                    'total' => $venta->total,
                    'items_procesados' => count($detallesVenta)
                ]);

                // Recargar venta con todas las relaciones
                return $this->ventaRepository->findById($venta->venta_id);
            });
        } catch (\Exception $e) {
            Log::error('Error en creación de venta completa', [
                'datos_venta' => $datosVenta,
                'detalles_count' => count($detallesVenta),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Actualizar venta completa
     */
    public function actualizarVentaCompleta(int $ventaId, array $datosVenta, array $detallesVenta): Venta
    {
        $venta = $this->ventaRepository->findById($ventaId);
        
        if (!$venta) {
            throw new \Exception("Venta no encontrada con ID: {$ventaId}");
        }

        // No se puede modificar si ya tiene factura autorizada
        if ($venta->facturaElectronica && $venta->facturaElectronica->estado_sri === 'AUTORIZADA') {
            throw new \Exception("No se puede modificar una venta con factura autorizada");
        }

        try {
            return DB::transaction(function () use ($venta, $datosVenta, $detallesVenta) {
                Log::info('Iniciando actualización de venta completa', [
                    'venta_id' => $venta->venta_id
                ]);

                // Revertir stock de productos de la venta original
                $this->revertirStockProductos($venta->detalles);

                // Validar nuevo stock
                $this->validarStockDisponible($detallesVenta);

                // Calcular nuevos totales
                $totales = $this->calcularTotales($detallesVenta);
                $datosVenta = array_merge($datosVenta, $totales);

                // Actualizar venta
                $ventaActualizada = $this->ventaRepository->updateVentaCompleta($venta->venta_id, $datosVenta, $detallesVenta);

                // Actualizar stock con nuevos valores
                $this->actualizarStockProductos($detallesVenta);

                // Actualizar ingreso
                $this->actualizarIngresoVenta($ventaActualizada);

                Log::info('Venta completa actualizada exitosamente', [
                    'venta_id' => $venta->venta_id,
                    'nuevo_total' => $ventaActualizada->total
                ]);

                return $ventaActualizada;
            });
        } catch (\Exception $e) {
            Log::error('Error en actualización de venta completa', [
                'venta_id' => $ventaId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Eliminar venta (soft delete) con reversión de stock
     */
    public function eliminarVenta(int $ventaId): bool
    {
        $venta = $this->ventaRepository->findById($ventaId);
        
        if (!$venta) {
            throw new \Exception("Venta no encontrada con ID: {$ventaId}");
        }

        // No se puede eliminar si ya tiene factura autorizada
        if ($venta->facturaElectronica && $venta->facturaElectronica->estado_sri === 'AUTORIZADA') {
            throw new \Exception("No se puede eliminar una venta con factura autorizada");
        }

        try {
            return DB::transaction(function () use ($venta) {
                Log::info('Iniciando eliminación de venta', [
                    'venta_id' => $venta->venta_id
                ]);

                // Revertir stock de productos
                $this->revertirStockProductos($venta->detalles);

                // Eliminar factura si existe y no está autorizada
                if ($venta->facturaElectronica && $venta->facturaElectronica->estado_sri !== 'AUTORIZADA') {
                    $venta->facturaElectronica->delete();
                }

                // Eliminar ingreso asociado
                $ingreso = $this->ingresoRepository->findByReference('venta', $venta->venta_id);
                if ($ingreso) {
                    $this->ingresoRepository->delete($ingreso->ingreso_id);
                }

                // Eliminar venta (soft delete)
                $resultado = $this->ventaRepository->delete($venta->venta_id);

                Log::info('Venta eliminada exitosamente', [
                    'venta_id' => $venta->venta_id
                ]);

                return $resultado;
            });
        } catch (\Exception $e) {
            Log::error('Error al eliminar venta', [
                'venta_id' => $ventaId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Obtener todas las ventas
     */
    public function getAll(): Collection
    {
        return $this->ventaRepository->getAll();
    }

    /**
     * Obtener venta por ID
     */
    public function getById(int $id): ?Venta
    {
        return $this->ventaRepository->findById($id);
    }

    /**
     * Obtener ventas por cliente
     */
    public function getPorCliente(int $clienteId): Collection
    {
        return $this->ventaRepository->getByClienteId($clienteId);
    }

    /**
     * Obtener ventas del día
     */
    public function getVentasDelDia(?string $fecha = null): Collection
    {
        return $this->ventaRepository->getVentasDelDia($fecha);
    }

    /**
     * Obtener estadísticas de ventas
     */
    public function getEstadisticas(): array
    {
        return $this->ventaRepository->getStats();
    }

    /**
     * VALIDACIÓN CRÍTICA: Validar stock disponible para productos
     */
    private function validarStockDisponible(array $detallesVenta): void
    {
        foreach ($detallesVenta as $detalle) {
            if ($detalle['vendible_type'] === 'App\Models\ProductoAutomotriz') {
                $producto = $this->productoAutomotrizRepository->findById($detalle['vendible_id']);
                
                if (!$producto) {
                    throw new \Exception("Producto automotriz no encontrado con ID: {$detalle['vendible_id']}");
                }

                if (!$producto->activo) {
                    throw new \Exception("El producto '{$producto->nombre}' no está activo");
                }

                if ($producto->stock < $detalle['cantidad']) {
                    throw new \Exception("Stock insuficiente para '{$producto->nombre}'. Stock disponible: {$producto->stock}, solicitado: {$detalle['cantidad']}");
                }
            } elseif ($detalle['vendible_type'] === 'App\Models\ProductoDespensa') {
                $producto = $this->productoDespensaRepository->findById($detalle['vendible_id']);
                
                if (!$producto) {
                    throw new \Exception("Producto de despensa no encontrado con ID: {$detalle['vendible_id']}");
                }

                if (!$producto->activo) {
                    throw new \Exception("El producto '{$producto->nombre}' no está activo");
                }

                if ($producto->stock < $detalle['cantidad']) {
                    throw new \Exception("Stock insuficiente para '{$producto->nombre}'. Stock disponible: {$producto->stock}, solicitado: {$detalle['cantidad']}");
                }
            }
            // Los servicios no requieren validación de stock
        }
    }

    /**
     * ACTUALIZACIÓN CRÍTICA: Actualizar stock de productos
     */
    private function actualizarStockProductos(array $detallesVenta): void
    {
        foreach ($detallesVenta as $detalle) {
            if ($detalle['vendible_type'] === 'App\Models\ProductoAutomotriz') {
                $producto = $this->productoAutomotrizRepository->findById($detalle['vendible_id']);
                $nuevoStock = $producto->stock - $detalle['cantidad'];
                
                $this->productoAutomotrizRepository->updateStock($detalle['vendible_id'], $nuevoStock);
                
                Log::info('Stock producto automotriz actualizado', [
                    'producto_id' => $detalle['vendible_id'],
                    'producto_nombre' => $producto->nombre,
                    'stock_anterior' => $producto->stock,
                    'cantidad_vendida' => $detalle['cantidad'],
                    'stock_nuevo' => $nuevoStock
                ]);
            } elseif ($detalle['vendible_type'] === 'App\Models\ProductoDespensa') {
                $producto = $this->productoDespensaRepository->findById($detalle['vendible_id']);
                $nuevoStock = $producto->stock - $detalle['cantidad'];
                
                $this->productoDespensaRepository->updateStock($detalle['vendible_id'], $nuevoStock);
                
                Log::info('Stock producto despensa actualizado', [
                    'producto_id' => $detalle['vendible_id'],
                    'producto_nombre' => $producto->nombre,
                    'stock_anterior' => $producto->stock,
                    'cantidad_vendida' => $detalle['cantidad'],
                    'stock_nuevo' => $nuevoStock
                ]);
            }
        }
    }

    /**
     * Revertir stock de productos (para actualizaciones y eliminaciones)
     */
    private function revertirStockProductos(Collection $detalles): void
    {
        foreach ($detalles as $detalle) {
            if ($detalle->vendible_type === 'App\Models\ProductoAutomotriz') {
                $producto = $this->productoAutomotrizRepository->findById($detalle->vendible_id);
                if ($producto) {
                    $nuevoStock = $producto->stock + $detalle->cantidad;
                    $this->productoAutomotrizRepository->updateStock($detalle->vendible_id, $nuevoStock);
                }
            } elseif ($detalle->vendible_type === 'App\Models\ProductoDespensa') {
                $producto = $this->productoDespensaRepository->findById($detalle->vendible_id);
                if ($producto) {
                    $nuevoStock = $producto->stock + $detalle->cantidad;
                    $this->productoDespensaRepository->updateStock($detalle->vendible_id, $nuevoStock);
                }
            }
        }
    }

    /**
     * CREACIÓN CRÍTICA: Crear ingreso automático desde venta
     */
    private function crearIngresoAutomatico(Venta $venta): void
    {
        $datosIngreso = [
            'descripcion' => "Ingreso por venta #{$venta->venta_id} - Cliente: {$venta->cliente->nombre}",
            'monto' => $venta->total,
            'fecha' => $venta->fecha,
            'tipo' => 'venta',
            'referencia_tipo' => 'venta',
            'referencia_id' => $venta->venta_id,
        ];

        $ingreso = $this->ingresoRepository->create($datosIngreso);

        Log::info('Ingreso automático creado', [
            'ingreso_id' => $ingreso->ingreso_id,
            'venta_id' => $venta->venta_id,
            'monto' => $venta->total
        ]);
    }

    /**
     * Actualizar ingreso asociado a la venta
     */
    private function actualizarIngresoVenta(Venta $venta): void
    {
        $ingreso = $this->ingresoRepository->findByReference('venta', $venta->venta_id);
        
        if ($ingreso) {
            $this->ingresoRepository->update($ingreso->ingreso_id, [
                'monto' => $venta->total,
                'descripcion' => "Ingreso por venta #{$venta->venta_id} - Cliente: {$venta->cliente->nombre} (Actualizado)"
            ]);
        }
    }

    /**
     * Calcular totales de la venta
     */
    private function calcularTotales(array $detallesVenta): array
    {
        $subtotal = 0;
        $iva = 0;
        
        foreach ($detallesVenta as $detalle) {
            $subtotalLinea = $detalle['precio_unitario'] * $detalle['cantidad'];
            $subtotal += $subtotalLinea;
            
            // En Ecuador, generalmente los servicios tienen IVA del 12%
            if ($detalle['vendible_type'] === 'App\Models\Servicio') {
                $iva += $subtotalLinea * 0.12;
            }
            // Los productos pueden tener IVA 0% o 12% según configuración
            // Por simplicidad, asumimos 0% para productos, pero esto debe ser configurable
        }
        
        $total = $subtotal + $iva;
        
        return [
            'subtotal' => round($subtotal, 2),
            'iva' => round($iva, 2),
            'total' => round($total, 2)
        ];
    }

    /**
     * Crear detalles de la venta
     */
    private function crearDetallesVenta(Venta $venta, array $detallesVenta): void
    {
        foreach ($detallesVenta as $detalleData) {
            $detalleData['venta_id'] = $venta->venta_id;
            $this->detalleRepository->create($detalleData);
        }
    }

    /**
     * Validaciones generales de la venta
     */
    private function validarVentaCompleta(array $datosVenta, array $detallesVenta): void
    {
        // Validar que hay detalles
        if (empty($detallesVenta)) {
            throw new \Exception("La venta debe tener al menos un detalle");
        }

        // Validar cliente
        if (empty($datosVenta['cliente_id'])) {
            throw new \Exception("La venta debe tener un cliente asociado");
        }

        // Validar que todos los detalles tengan los campos requeridos
        foreach ($detallesVenta as $index => $detalle) {
            if (empty($detalle['vendible_type']) || empty($detalle['vendible_id'])) {
                throw new \Exception("Detalle #{$index}: vendible_type y vendible_id son obligatorios");
            }
            
            if (empty($detalle['cantidad']) || $detalle['cantidad'] <= 0) {
                throw new \Exception("Detalle #{$index}: la cantidad debe ser mayor a 0");
            }
            
            if (empty($detalle['precio_unitario']) || $detalle['precio_unitario'] <= 0) {
                throw new \Exception("Detalle #{$index}: el precio unitario debe ser mayor a 0");
            }
        }
    }
}
