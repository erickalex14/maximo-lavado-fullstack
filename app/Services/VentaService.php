<?php

namespace App\Services;

use App\Contracts\VentaRepositoryInterface;
use App\Contracts\VentaDetalleRepositoryInterface;
use App\Contracts\IngresoRepositoryInterface;
use App\Contracts\ProductoAutomotrizRepositoryInterface;
use App\Contracts\ProductoDespensaRepositoryInterface;
use App\Contracts\LavadoRepositoryInterface;
use App\Contracts\ServicioRepositoryInterface;
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
    protected LavadoRepositoryInterface $lavadoRepository;
    protected ServicioRepositoryInterface $servicioRepository;

    public function __construct(
        VentaRepositoryInterface $ventaRepository,
        VentaDetalleRepositoryInterface $detalleRepository,
        IngresoRepositoryInterface $ingresoRepository,
        ProductoAutomotrizRepositoryInterface $productoAutomotrizRepository,
        ProductoDespensaRepositoryInterface $productoDespensaRepository,
        FacturaElectronicaService $facturaElectronicaService,
        LavadoRepositoryInterface $lavadoRepository,
        ServicioRepositoryInterface $servicioRepository
    ) {
        $this->ventaRepository = $ventaRepository;
        $this->detalleRepository = $detalleRepository;
        $this->ingresoRepository = $ingresoRepository;
        $this->productoAutomotrizRepository = $productoAutomotrizRepository;
        $this->productoDespensaRepository = $productoDespensaRepository;
        $this->facturaElectronicaService = $facturaElectronicaService;
        $this->lavadoRepository = $lavadoRepository;
        $this->servicioRepository = $servicioRepository;
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

                // PASO 8: CREAR LAVADOS AUTOMÁTICOS SI HAY SERVICIOS ⚡ CRÍTICO
                $lavadosCreados = [];
                if ($this->tieneServicios($detallesVenta)) {
                    $lavadosCreados = $this->crearLavadosDesdeVenta($venta->fresh(['detalles']));
                    Log::info('Lavados automáticos creados', [
                        'venta_id' => $venta->venta_id,
                        'lavados_creados' => count($lavadosCreados)
                    ]);
                }

                // PASO 9: PROCESAR FACTURA CON SRI (opcional, puede ser asíncrono)
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

                Log::info('✅ Venta completa creada exitosamente con flujo automático', [
                    'venta_id' => $venta->venta_id,
                    'factura_id' => $facturaElectronica->factura_electronica_id,
                    'total' => $venta->total,
                    'items_procesados' => count($detallesVenta),
                    'lavados_creados' => count($lavadosCreados),
                    'tiene_productos' => $this->tieneProductos($detallesVenta),
                    'tiene_servicios' => $this->tieneServicios($detallesVenta)
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
            if ($detalle['tipo_item'] === 'producto_automotriz') {
                $producto = $this->productoAutomotrizRepository->findById($detalle['item_id']);
                
                if (!$producto) {
                    throw new \Exception("Producto automotriz no encontrado con ID: {$detalle['item_id']}");
                }

                if (!$producto->activo) {
                    throw new \Exception("El producto '{$producto->nombre}' no está activo");
                }

                if ($producto->stock < $detalle['cantidad']) {
                    throw new \Exception("Stock insuficiente para '{$producto->nombre}'. Stock disponible: {$producto->stock}, solicitado: {$detalle['cantidad']}");
                }
            } elseif ($detalle['tipo_item'] === 'producto_despensa') {
                $producto = $this->productoDespensaRepository->findById($detalle['item_id']);
                
                if (!$producto) {
                    throw new \Exception("Producto de despensa no encontrado con ID: {$detalle['item_id']}");
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
            if ($detalle['tipo_item'] === 'producto_automotriz') {
                $producto = $this->productoAutomotrizRepository->findById($detalle['item_id']);
                $nuevoStock = $producto->stock - $detalle['cantidad'];
                
                $this->productoAutomotrizRepository->updateStock($detalle['item_id'], $nuevoStock);
                
                Log::info('Stock producto automotriz actualizado', [
                    'producto_id' => $detalle['item_id'],
                    'producto_nombre' => $producto->nombre,
                    'stock_anterior' => $producto->stock,
                    'cantidad_vendida' => $detalle['cantidad'],
                    'stock_nuevo' => $nuevoStock
                ]);
            } elseif ($detalle['tipo_item'] === 'producto_despensa') {
                $producto = $this->productoDespensaRepository->findById($detalle['item_id']);
                $nuevoStock = $producto->stock - $detalle['cantidad'];
                
                $this->productoDespensaRepository->updateStock($detalle['item_id'], $nuevoStock);
                
                Log::info('Stock producto despensa actualizado', [
                    'producto_id' => $detalle['item_id'],
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
            if ($detalle->tipo_item === 'producto_automotriz') {
                $producto = $this->productoAutomotrizRepository->findById($detalle->item_id);
                if ($producto) {
                    $nuevoStock = $producto->stock + $detalle->cantidad;
                    $this->productoAutomotrizRepository->updateStock($detalle->item_id, $nuevoStock);
                }
            } elseif ($detalle->tipo_item === 'producto_despensa') {
                $producto = $this->productoDespensaRepository->findById($detalle->item_id);
                if ($producto) {
                    $nuevoStock = $producto->stock + $detalle->cantidad;
                    $this->productoDespensaRepository->updateStock($detalle->item_id, $nuevoStock);
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

            // ⚡ CRÍTICO: IVA 15% (actualizado) para items gravados estándar
            // Ajuste temporal: El IVA general pasó de 12% a 15%
            if (in_array($detalle['tipo_item'], ['servicio', 'producto_automotriz', 'producto_despensa'])) {
                $iva += $subtotalLinea * 0.15;
            }

            // TODO: Parametrizar % IVA desde configuración/base de datos y soportar tarifas 0%, 8%, etc.
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
            if (empty($detalle['tipo_item']) || empty($detalle['item_id'])) {
                throw new \Exception("Detalle #{$index}: tipo_item e item_id son obligatorios");
            }
            
            if (empty($detalle['cantidad']) || $detalle['cantidad'] <= 0) {
                throw new \Exception("Detalle #{$index}: la cantidad debe ser mayor a 0");
            }
            
            if (empty($detalle['precio_unitario']) || $detalle['precio_unitario'] <= 0) {
                throw new \Exception("Detalle #{$index}: el precio unitario debe ser mayor a 0");
            }
        }
    }

    /**
     * Verificar si la venta contiene servicios
     */
    private function tieneServicios(array $detallesVenta): bool
    {
        foreach ($detallesVenta as $detalle) {
            if ($detalle['tipo_item'] === 'servicio') {
                return true;
            }
        }
        return false;
    }

    /**
     * Verificar si la venta contiene productos
     */
    private function tieneProductos(array $detallesVenta): bool
    {
        foreach ($detallesVenta as $detalle) {
            if (in_array($detalle['tipo_item'], [
                'producto_automotriz', 
                'producto_despensa'
            ])) {
                return true;
            }
        }
        return false;
    }

    /**
     * ⚡ MÉTODO CRÍTICO: Crear lavados automáticos desde una venta con servicios
     * Usado internamente para flujos automáticos sin dependencia circular
     * 
     * @param \App\Models\Venta $venta La venta que contiene servicios
     * @return array Array de lavados creados
     */
    private function crearLavadosDesdeVenta($venta): array
    {
        try {
            Log::info('⚡ Iniciando creación de lavados desde venta', [
                'venta_id' => $venta->venta_id,
                'cliente_id' => $venta->cliente_id,
                'total_detalles' => $venta->detalles->count()
            ]);

            $lavadosCreados = [];
            
            // Filtrar solo los detalles que son servicios
            $detallesServicios = $venta->detalles->where('tipo_item', 'servicio');
            
            if ($detallesServicios->isEmpty()) {
                Log::info('No se encontraron servicios en la venta, no se crean lavados', [
                    'venta_id' => $venta->venta_id
                ]);
                return $lavadosCreados;
            }

            // Crear un lavado por cada servicio en la venta
            foreach ($detallesServicios as $detalle) {
                $servicio = $this->servicioRepository->findById($detalle->item_id);
                
                if (!$servicio) {
                    Log::warning('Servicio no encontrado para crear lavado', [
                        'servicio_id' => $detalle->item_id,
                        'venta_id' => $venta->venta_id
                    ]);
                    continue;
                }
                
                // Datos del lavado basados en la venta y el servicio
                $dataLavado = [
                    'venta_id' => $venta->venta_id,
                    'cliente_id' => $venta->cliente_id,
                    'empleado_id' => $venta->usuario_id ?? null, // Usar usuario_id en lugar de empleado_id
                    'servicio_id' => $servicio->servicio_id,
                    'vehiculo_id' => null, // Se puede obtener del cliente o asignar después
                    'tipo_vehiculo_id' => null, // Se puede inferir del servicio
                    'estado' => 'PENDIENTE', // Estado inicial
                    'observaciones' => "Lavado creado automáticamente desde venta #{$venta->venta_id}",
                    'fecha' => $venta->fecha ?? now(),
                    'precio' => $detalle->precio_unitario,
                    'cantidad_servicios' => $detalle->cantidad,
                    
                    // Auditoría
                    'creado_desde_venta' => true,
                    'sistema_origen' => 'V2_AUTOMATICO',
                    'created_at' => now(),
                    'updated_at' => now()
                ];

                // Intentar obtener vehículo del cliente
                if ($venta->cliente && $venta->cliente->vehiculos->isNotEmpty()) {
                    $vehiculoPrincipal = $venta->cliente->vehiculos->first();
                    $dataLavado['vehiculo_id'] = $vehiculoPrincipal->vehiculo_id;
                    $dataLavado['tipo_vehiculo_id'] = $vehiculoPrincipal->tipo_vehiculo_id;
                }

                                // Crear el registro de lavado usando el repository
                $lavado = $this->lavadoRepository->create($dataLavado);
                $lavadosCreados[] = $lavado;

                // Obtener el ID del lavado de forma más defensiva
                $lavadoId = null;
                $lavadoEstado = null;

                if (is_array($lavado)) {
                    $lavadoId = $lavado['lavado_id'] ?? $lavado['id'] ?? 'ID no encontrado';
                    $lavadoEstado = $lavado['estado'] ?? 'Estado no encontrado';
                } else if (is_object($lavado)) {
                    $lavadoId = $lavado->lavado_id ?? $lavado->id ?? 'ID no encontrado';
                    $lavadoEstado = $lavado->estado ?? 'Estado no encontrado';
                }

                Log::info('Lavado creado desde venta', [
                    'lavado_id' => $lavadoId,
                    'venta_id' => $venta->venta_id,
                    'servicio_id' => $servicio->servicio_id,
                    'servicio_nombre' => $servicio->nombre,
                    'estado' => $lavadoEstado
                ]);
            }

            Log::info('✅ Lavados creados exitosamente desde venta', [
                'venta_id' => $venta->venta_id,
                'lavados_creados' => count($lavadosCreados),
                'servicios_procesados' => $detallesServicios->count()
            ]);

            return $lavadosCreados;

        } catch (\Exception $e) {
            Log::error('❌ Error al crear lavados desde venta', [
                'venta_id' => $venta->venta_id ?? 'desconocido',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw new \Exception("Error al crear lavados automáticos: {$e->getMessage()}");
        }
    }
}
