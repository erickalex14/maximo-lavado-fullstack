// Servicios principales del sistema de lavado de autos
// Exporta todas las instancias de servicios ya configuradas y listas para usar

// Servicios de autenticación y dashboard
export { default as authService } from './AuthService';
export { default as dashboardService } from './DashboardService';

// Servicios de gestión de entidades principales
export { default as empleadoService } from './EmpleadoService';
export { default as clienteService } from './ClienteService';
export { default as vehiculoService } from './VehiculoService';
export { default as lavadoService } from './LavadoService';

// Servicios de productos
export { default as productoService } from './ProductoService';

// Servicios de proveedores (consolidado)
export { default as proveedorService } from './ProveedorService';

// Servicios financieros
export { default as ingresoService } from './IngresoService';
export { default as egresoService } from './EgresoService';
export { default as gastoGeneralService } from './GastoGeneralService';
export { default as balanceService } from './BalanceService';

// Servicios de facturación y ventas
export { default as facturaService } from './FacturaService';
export { default as ventaService } from './VentaService';

// Servicios de reportes
export { default as reporteService } from './ReporteService';

// Servicio base (para extensión)
export { default as BaseService } from './BaseService';
