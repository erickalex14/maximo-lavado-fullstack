// ========================================================
// 🔥 TIPOS TYPESCRIPT - CONSISTENTES CON MIGRACIONES
// ========================================================

// Tipos base del sistema
export interface BaseModel {
  created_at: string;
  updated_at: string;
  deleted_at?: string | null;
}

// ========================================================
// 👤 USUARIOS Y AUTENTICACIÓN
// ========================================================

// Usuario - Tabla: users
export interface User extends BaseModel {
  id: number;
  name: string;
  email: string;
  email_verified_at?: string | null;
}

// ========================================================
// 📊 ENTIDADES PRINCIPALES - SISTEMA LEGACY
// ========================================================

// Cliente - Tabla: clientes
export interface Cliente extends BaseModel {
  cliente_id: number;
  nombre: string;
  telefono: string;
  email: string;
  direccion?: string | null;
  cedula: string;
  // Relaciones
  vehiculos?: Vehiculo[];
  facturas?: Factura[];
}

// Empleado - Tabla: empleados
export interface Empleado extends BaseModel {
  empleado_id: number;
  nombres: string;
  apellidos: string;
  telefono: string;
  cedula: string;
  tipo_salario: 'mensual' | 'diario' | 'quincenal' | 'semanal';
  salario: number;
  // Relaciones
  lavados?: Lavado[];
}

// Tipo de Vehículo - Tabla: tipos_vehiculos (Sistema V2)
export interface TipoVehiculo extends BaseModel {
  id: number;
  nombre: string;
  descripcion?: string | null;
  activo: boolean;
  // Relaciones
  vehiculos?: Vehiculo[];
  servicios_precios?: ServicioPrecio[];
}

// Vehículo - Tabla: vehiculos
export interface Vehiculo extends BaseModel {
  vehiculo_id: number;
  cliente_id: number;
  tipo_vehiculo_id: number;
  matricula?: string | null;
  descripcion?: string | null;
  // Relaciones
  cliente?: Cliente;
  tipo_vehiculo?: TipoVehiculo;
  lavados?: Lavado[];
}

// Servicio - Tabla: servicios (Sistema V2)
export interface Servicio extends BaseModel {
  id: number;
  nombre: string;
  descripcion?: string | null;
  precio_base: number;
  duracion_estimada?: number | null;
  activo: boolean;
  // Relaciones
  precios?: ServicioPrecio[];
  lavados?: Lavado[];
}

// Precio de Servicio - Tabla: servicio_precios (Sistema V2)
export interface ServicioPrecio extends BaseModel {
  id: number;
  servicio_id: number;
  tipo_vehiculo_id: number;
  precio: number;
  // Relaciones
  servicio?: Servicio;
  tipo_vehiculo?: TipoVehiculo;
}

// Lavado - Tabla: lavados
export interface Lavado extends BaseModel {
  lavado_id: number;
  vehiculo_id: number;
  empleado_id: number;
  fecha: string;
  tipo_lavado: 'completo' | 'solo_fuera' | 'solo_por_dentro';
  precio: number;
  pulverizado: boolean;
  servicio_id?: number | null;
  // Relaciones
  vehiculo?: Vehiculo;
  empleado?: Empleado;
  servicio?: Servicio;
  detalles_factura?: FacturaDetalle[];
}

// Producto Automotriz - Tabla: productos_automotrices
export interface ProductoAutomotriz extends BaseModel {
  producto_automotriz_id: number;
  codigo: string;
  nombre: string;
  descripcion: string;
  precio_venta: number;
  stock: number;
  activo: boolean;
  // Relaciones
  detalles_factura?: FacturaDetalle[];
}

// Producto Despensa - Tabla: productos_despensa
export interface ProductoDespensa extends BaseModel {
  producto_despensa_id: number;
  nombre: string;
  descripcion: string;
  precio_venta: number;
  stock: number;
  activo: boolean;
  // Relaciones
  detalles_factura?: FacturaDetalle[];
}

// Proveedor - Tabla: proveedores
export interface Proveedor extends BaseModel {
  proveedor_id: number;
  nombre: string;
  email?: string | null;
  descripcion?: string | null;
  telefono?: string | null;
  deuda_pendiente: number;
  // Relaciones
  pagos?: PagoProveedor[];
}

// Pago Proveedor - Tabla: pagos_proveedores
export interface PagoProveedor extends BaseModel {
  id_pago_proveedor: number;
  proveedor_id: number;
  monto: number;
  fecha: string;
  descripcion?: string | null;
  // Relaciones
  proveedor?: Proveedor;
}

// Ingreso - Tabla: ingresos
export interface Ingreso extends BaseModel {
  ingreso_id: number;
  fecha: string;
  tipo: 'venta' | 'servicio';
  referencia_id?: number | null;
  monto: number;
  descripcion?: string | null;
  // Relaciones dinámicas según el tipo
  venta?: Venta;
  lavado?: Lavado;
}

// Egreso - Tabla: egresos
export interface Egreso extends BaseModel {
  egreso_id: number;
  fecha: string;
  tipo: 'salario' | 'proveedor' | 'gasto_general';
  referencia_id?: number | null;
  monto: number;
  descripcion?: string | null;
  // Relaciones dinámicas según el tipo
  empleado?: Empleado;
  pago_proveedor?: PagoProveedor;
  gasto_general?: GastoGeneral;
}

// Gasto General - Tabla: gastos_generales
export interface GastoGeneral extends BaseModel {
  gasto_general_id: number;
  nombre: string;
  descripcion?: string | null;
  monto: number;
  fecha: string;
}

// Factura - Tabla: facturas
export interface Factura extends BaseModel {
  factura_id: number;
  numero_factura: string;
  cliente_id: number;
  fecha: string;
  descripcion?: string | null;
  total: number;
  // Relaciones
  cliente?: Cliente;
  detalles?: FacturaDetalle[];
}

// Detalle de Factura - Tabla: facturas_detalles
export interface FacturaDetalle extends BaseModel {
  factura_detalle_id: number;
  factura_id: number;
  lavado_id?: number | null;
  producto_automotriz_id?: number | null;
  producto_despensa_id?: number | null;
  cantidad: number;
  precio_unitario: number;
  subtotal: number;
  // Relaciones
  factura?: Factura;
  lavado?: Lavado;
  producto_automotriz?: ProductoAutomotriz;
  producto_despensa?: ProductoDespensa;
}

// ========================================================
// 🔥 SISTEMA UNIFICADO V2 - ENTIDADES PRINCIPALES
// ========================================================

// Venta Unificada - Tabla: ventas (Sistema V2)
export interface Venta extends BaseModel {
  id: number;
  cliente_id?: number | null;
  empleado_id?: number | null;
  vehiculo_id?: number | null;
  tipo: 'servicio' | 'producto_automotriz' | 'producto_despensa';
  referencia_id: number;
  cantidad: number;
  precio_unitario: number;
  total: number;
  fecha: string;
  descripcion?: string | null;
  // Relaciones
  cliente?: Cliente;
  empleado?: Empleado;
  vehiculo?: Vehiculo;
  servicio?: Servicio;
  producto_automotriz?: ProductoAutomotriz;
  producto_despensa?: ProductoDespensa;
  factura_electronica?: FacturaElectronica;
}

// Detalle de Venta - Tabla: venta_detalles (Sistema V2)
export interface VentaDetalle extends BaseModel {
  id: number;
  venta_id: number;
  tipo: 'servicio' | 'producto_automotriz' | 'producto_despensa';
  referencia_id: number;
  cantidad: number;
  precio_unitario: number;
  subtotal: number;
  // Relaciones
  venta?: Venta;
  servicio?: Servicio;
  producto_automotriz?: ProductoAutomotriz;
  producto_despensa?: ProductoDespensa;
}

// Factura Electrónica - Tabla: facturas_electronicas (Sistema SRI)
export interface FacturaElectronica extends BaseModel {
  id: number;
  venta_id: number;
  numero_factura: string;
  autorizacion?: string | null;
  fecha_emision: string;
  fecha_autorizacion?: string | null;
  estado: 'borrador' | 'autorizada' | 'anulada' | 'error';
  xml_firmado?: string | null;
  clave_acceso?: string | null;
  errores_sri?: string | string[] | null;
  // Datos del cliente
  cliente_razon_social: string;
  cliente_identificacion: string;
  cliente_direccion?: string | null;
  cliente_email?: string | null;
  cliente_telefono?: string | null;
  // Totales
  subtotal_0: number;
  subtotal_12: number;
  iva: number;
  total: number;
  // Relaciones
  venta?: Venta;
}

// ========================================================
// 📝 FORMULARIOS DE CREACIÓN Y ACTUALIZACIÓN
// ========================================================

// Formularios para Usuarios
export interface CreateUserForm {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
}

export interface UpdateUserForm {
  name?: string;
  email?: string;
  password?: string;
  password_confirmation?: string;
}

// Formularios para Clientes
export interface CreateClienteRequest {
  nombre: string;
  telefono: string;
  email: string;
  direccion?: string;
  cedula: string;
}

export interface UpdateClienteRequest extends Partial<CreateClienteRequest> {}

// Formularios para Empleados
export interface CreateEmpleadoRequest {
  nombres: string;
  apellidos: string;
  telefono: string;
  cedula: string;
  tipo_salario: 'mensual' | 'diario' | 'quincenal' | 'semanal';
  salario: number;
}

export interface UpdateEmpleadoRequest extends Partial<CreateEmpleadoRequest> {}

// Formularios para Vehículos
export interface CreateVehiculoRequest {
  cliente_id: number;
  tipo_vehiculo_id: number;
  matricula?: string;
  descripcion?: string;
}

export interface UpdateVehiculoRequest extends Partial<CreateVehiculoRequest> {}

// Formularios para Tipos de Vehículos
export interface CreateTipoVehiculoRequest {
  nombre: string;
  descripcion?: string;
  activo?: boolean;
}

export interface UpdateTipoVehiculoRequest extends Partial<CreateTipoVehiculoRequest> {}

// Formularios para Servicios
export interface CreateServicioRequest {
  nombre: string;
  descripcion?: string;
  precio_base: number;
  duracion_estimada?: number;
  activo?: boolean;
  precios?: Array<{
    tipo_vehiculo_id: number;
    precio: number;
  }>;
}

export interface UpdateServicioRequest extends Partial<CreateServicioRequest> {}

// Formularios para Lavados
export interface CreateLavadoRequest {
  vehiculo_id: number;
  empleado_id: number;
  fecha: string;
  tipo_lavado: 'completo' | 'solo_fuera' | 'solo_por_dentro';
  precio: number;
  pulverizado?: boolean;
  servicio_id?: number;
}

export interface UpdateLavadoRequest extends Partial<CreateLavadoRequest> {}

// Formularios para Productos Automotrices
export interface CreateProductoAutomotrizRequest {
  codigo: string;
  nombre: string;
  descripcion: string;
  precio_venta: number;
  stock: number;
  activo?: boolean;
}

export interface UpdateProductoAutomotrizRequest extends Partial<CreateProductoAutomotrizRequest> {}

// Formularios para Productos de Despensa
export interface CreateProductoDespensaRequest {
  nombre: string;
  descripcion: string;
  precio_venta: number;
  stock: number;
  activo?: boolean;
}

export interface UpdateProductoDespensaRequest extends Partial<CreateProductoDespensaRequest> {}

// Formularios para Proveedores
export interface CreateProveedorRequest {
  nombre: string;
  email?: string;
  descripcion?: string;
  telefono?: string;
  deuda_pendiente?: number;
}

export interface UpdateProveedorRequest extends Partial<CreateProveedorRequest> {}

// Formularios para Pagos a Proveedores
export interface CreatePagoProveedorRequest {
  proveedor_id: number;
  monto: number;
  fecha: string;
  descripcion?: string;
}

export interface UpdatePagoProveedorRequest extends Partial<CreatePagoProveedorRequest> {}

// Formularios para Ingresos
export interface CreateIngresoRequest {
  fecha: string;
  tipo: 'venta' | 'servicio';
  referencia_id?: number;
  monto: number;
  descripcion?: string;
}

export interface UpdateIngresoRequest extends Partial<CreateIngresoRequest> {}

// Formularios para Egresos
export interface CreateEgresoRequest {
  fecha: string;
  tipo: 'salario' | 'proveedor' | 'gasto_general';
  referencia_id?: number;
  monto: number;
  descripcion?: string;
}

export interface UpdateEgresoRequest extends Partial<CreateEgresoRequest> {}

// Formularios para Gastos Generales
export interface CreateGastoGeneralRequest {
  nombre: string;
  descripcion?: string;
  monto: number;
  fecha: string;
}

export interface UpdateGastoGeneralRequest extends Partial<CreateGastoGeneralRequest> {}

// Formularios para Ventas Unificadas
export interface CreateVentaRequest {
  cliente_id?: number;
  empleado_id?: number;
  vehiculo_id?: number;
  tipo: 'servicio' | 'producto_automotriz' | 'producto_despensa';
  referencia_id: number;
  cantidad: number;
  precio_unitario?: number;
  descripcion?: string;
  generar_factura?: boolean;
}

export interface UpdateVentaRequest extends Partial<CreateVentaRequest> {}

// Formularios para Facturas Electrónicas
export interface CreateFacturaElectronicaRequest {
  venta_id: number;
  cliente_razon_social?: string;
  cliente_identificacion?: string;
  cliente_direccion?: string;
  cliente_email?: string;
  cliente_telefono?: string;
}

export interface UpdateFacturaElectronicaRequest extends Partial<CreateFacturaElectronicaRequest> {}

// ========================================================
// 🔍 FILTROS Y PAGINACIÓN
// ========================================================

// Tipos base para respuestas de API
export interface ApiResponse<T = any> {
  success: boolean;
  message: string;
  data?: T;
  errors?: Record<string, string[]>;
}

export interface PaginatedResponse<T = any> {
  data: T[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from: number;
  to: number;
}

// Filtros base
export interface FilterOptions {
  search?: string;
  page?: number;
  per_page?: number;
  sort_by?: string;
  sort_direction?: 'asc' | 'desc';
  fecha_inicio?: string;
  fecha_fin?: string;
}

// Filtros específicos por entidad
export interface EmpleadoFilters extends FilterOptions {
  tipo_salario?: 'mensual' | 'diario' | 'quincenal' | 'semanal';
}

export interface VehiculoFilters extends FilterOptions {
  cliente_id?: number;
  tipo_vehiculo_id?: number;
}

export interface LavadoFilters extends FilterOptions {
  empleado_id?: number;
  vehiculo_id?: number;
  cliente_id?: number;
  servicio_id?: number;
  tipo_lavado?: 'completo' | 'solo_fuera' | 'solo_por_dentro';
  pulverizado?: boolean;
}

export interface ProductoFilters extends FilterOptions {
  activo?: boolean;
  categoria?: string;
}

export interface ProveedorFilters extends FilterOptions {
  deuda_pendiente?: boolean;
}

export interface VentaFilters extends FilterOptions {
  tipo?: 'servicio' | 'producto_automotriz' | 'producto_despensa';
  cliente_id?: number;
  empleado_id?: number;
  vehiculo_id?: number;
  servicio_id?: number;
  producto_id?: number;
}

export interface IngresoFilters extends FilterOptions {
  tipo?: 'venta' | 'servicio';
  referencia_id?: number;
}

export interface EgresoFilters extends FilterOptions {
  tipo?: 'salario' | 'proveedor' | 'gasto_general';
  referencia_id?: number;
}

export interface GastoGeneralFilters extends FilterOptions {
  nombre?: string;
}

export interface FacturaElectronicaFilters extends FilterOptions {
  estado?: 'borrador' | 'autorizada' | 'anulada' | 'error';
  venta_id?: number;
  cliente_identificacion?: string;
  numero_factura?: string;
  fecha_emision_inicio?: string;
  fecha_emision_fin?: string;
}

// ========================================================
// 📊 DASHBOARD Y MÉTRICAS
// ========================================================

export interface DashboardMetrics {
  total_lavados_hoy: number;
  ingresos_hoy: number;
  total_clientes: number;
  total_empleados: number;
  lavados_pendientes: number;
  facturas_pendientes: number;
}

export interface DashboardChartData {
  lavados_por_dia: Array<{ fecha: string; cantidad: number }>;
  ingresos_por_mes: Array<{ mes: string; monto: number }>;
  productos_mas_vendidos: Array<{ nombre: string; cantidad: number }>;
}

// ========================================================
// 🔐 AUTENTICACIÓN
// ========================================================

export interface LoginCredentials {
  email: string;
  password: string;
  remember?: boolean;
}

export interface AuthUser extends User {
  permissions?: string[];
  roles?: string[];
}

// ========================================================
// 📊 ESTADÍSTICAS Y REPORTES
// ========================================================

export interface UserStats {
  total_users: number;
  active_users: number;
  verified_users: number;
  unverified_users: number;
  deleted_users: number;
  users_this_month: number;
  users_last_month: number;
  growth_percentage: number;
}

// ========================================================
// 📋 REPORTES ESPECÍFICOS
// ========================================================

export interface ReporteVentas {
  resumen: {
    total_ventas: number;
    ingresos_totales: number;
    ticket_promedio: number;
    crecimiento_mensual: number;
  };
  ventas_por_categoria: Array<{
    categoria: string;
    cantidad: number;
    total: number;
    porcentaje: number;
  }>;
  tendencia_diaria: Array<{
    fecha: string;
    ventas: number;
    ingresos: number;
  }>;
  productos_top: Array<{
    producto: string;
    cantidad: number;
    ingresos: number;
  }>;
}

export interface ReporteLavados {
  resumen: {
    total_lavados: number;
    ingresos_lavados: number;
    promedio_por_lavado: number;
    lavados_con_pulverizado: number;
  };
  lavados_por_tipo: Array<{
    tipo: string;
    cantidad: number;
    porcentaje: number;
  }>;
  performance_empleados: Array<{
    empleado: string;
    lavados_realizados: number;
    ingresos_generados: number;
  }>;
  tendencia_diaria: Array<{
    fecha: string;
    cantidad: number;
    ingresos: number;
  }>;
}

export interface ReporteFinanciero {
  resumen: {
    total_ingresos: number;
    total_egresos: number;
    utilidad_neta: number;
    margen_ganancia: number;
  };
  flujo_caja: Array<{
    fecha: string;
    ingresos: number;
    egresos: number;
    saldo: number;
  }>;
  comparativo_mensual: Array<{
    mes: string;
    ingresos: number;
    egresos: number;
    utilidad: number;
  }>;
  principales_gastos: Array<{
    categoria: string;
    total: number;
    porcentaje: number;
  }>;
}

// Reportes adicionales (estructura base genérica hasta refinar con backend)
export interface ReporteIngresos {
  resumen: { total_ingresos: number; promedio_diario?: number };
  ingresos_por_dia?: Array<{ fecha: string; monto: number }>;
  ingresos_por_tipo?: Array<{ tipo: string; cantidad: number; total: number; porcentaje: number }>;
}

export interface ReporteEgresos {
  resumen: { total_egresos: number; promedio_diario?: number };
  egresos_por_dia?: Array<{ fecha: string; monto: number }>;
  egresos_por_tipo?: Array<{ tipo: string; cantidad: number; total: number; porcentaje: number }>;
}

export interface ReporteFacturas {
  resumen: { total_facturas: number; autorizadas: number; anuladas: number; pendientes: number };
  facturas_por_estado?: Array<{ estado: string; cantidad: number; porcentaje: number }>;
  facturacion_diaria?: Array<{ fecha: string; total: number }>;
}

export interface ReporteEmpleados {
  resumen: { total_empleados: number; activos: number; lavados_promedio?: number };
  productividad?: Array<{ empleado: string; lavados: number; ingresos?: number }>;
}

export interface ReporteProductos {
  resumen: { total_productos: number; productos_activos: number; ingresos_total?: number };
  top_productos?: Array<{ producto: string; cantidad: number; ingresos: number }>; 
  inventario_bajo?: Array<{ producto: string; stock: number }>; 
}

export interface ReporteClientes {
  resumen: { total_clientes: number; nuevos_periodo?: number; tickets_promedio?: number };
  clientes_frecuentes?: Array<{ cliente: string; compras: number; total: number }>;
}

export interface ReporteBalance {
  resumen: { ingresos: number; egresos: number; utilidad: number };
  balance_mensual?: Array<{ mes: string; ingresos: number; egresos: number; utilidad: number }>;
}

export interface ReporteCompleto {
  ventas?: ReporteVentas;
  lavados?: ReporteLavados;
  ingresos?: ReporteIngresos;
  egresos?: ReporteEgresos;
  facturas?: ReporteFacturas;
  empleados?: ReporteEmpleados;
  productos?: ReporteProductos;
  clientes?: ReporteClientes;
  financiero?: ReporteFinanciero;
  balance?: ReporteBalance;
}

export interface ReporteDisponible { tipo: string; descripcion: string }

export interface ReporteRequest {
  fecha_inicio: string;
  fecha_fin: string;
  formato: 'json' | 'pdf' | 'excel';
}
