// Tipos base del sistema
export interface BaseModel {
  created_at: string;
  updated_at: string;
  deleted_at?: string | null;
}

// Usuario
export interface User extends BaseModel {
  id: number;
  name: string;
  email: string;
  email_verified_at?: string | null;
}

// Form interfaces para usuarios
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

export interface UpdatePasswordForm {
  current_password: string;
  new_password: string;
  new_password_confirmation: string;
}

export interface ResetPasswordForm {
  new_password: string;
  new_password_confirmation: string;
}

// Estadísticas de usuarios
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

// Cliente
export interface Cliente extends BaseModel {
  cliente_id: number;
  nombre: string;
  telefono: string;
  email: string;
  direccion?: string;
  cedula: string;
  vehiculos?: Vehiculo[];
  vehiculos_count?: number;
  facturas?: Factura[];
  // Soft delete simulation for active/inactive
  activo?: boolean; // Computed field based on deleted_at
}

// Form interfaces
export interface CreateClienteForm {
  nombre: string;
  telefono: string;
  email: string;
  direccion?: string;
  cedula: string;
}

export interface UpdateClienteForm extends Partial<CreateClienteForm> {}

// Empleado - Exactamente como en el modelo Laravel y migración
export interface Empleado extends BaseModel {
  empleado_id: number;
  nombres: string;
  apellidos: string;
  telefono: string;
  cedula: string;
  tipo_salario: 'mensual' | 'diario' | 'quincenal' | 'semanal'; // Enum exacto de la migración
  salario: number;
  lavados?: Lavado[];
}

// Vehículo - Con lógica de negocio: matricula nullable solo para motos
export interface Vehiculo extends BaseModel {
  vehiculo_id: number;
  cliente_id: number;
  tipo: 'moto' | 'camioneta' | 'auto_pequeno' | 'auto_mediano'; // Enum exacto de la migración
  matricula?: string | null; // Nullable solo para motos según la lógica de negocio
  descripcion?: string | null; // Nullable según la migración
  cliente?: Cliente;
  lavados?: Lavado[];
}

// Lavado - Exactamente como en el modelo Laravel y migración
export interface Lavado extends BaseModel {
  lavado_id: number;
  vehiculo_id: number;
  empleado_id: number;
  fecha: string;
  tipo_lavado: 'completo' | 'solo_fuera' | 'solo_por_dentro'; // Enum exacto de la migración
  precio: number;
  pulverizado: boolean; // Booleano con default false
  vehiculo?: Vehiculo;
  empleado?: Empleado;
  detalles_factura?: FacturaDetalle[];
}

// Producto Automotriz - Exactamente como en el modelo Laravel
export interface ProductoAutomotriz extends BaseModel {
  producto_automotriz_id: number;
  codigo: string; // Código único del producto
  nombre: string;
  descripcion: string;
  precio_venta: number; // Precio de venta
  stock: number; // Nivel de stock
  activo: boolean; // Estado del producto (activo/inactivo)
  detalles_factura?: FacturaDetalle[];
}

// Producto Despensa - Exactamente como en el modelo Laravel
export interface ProductoDespensa extends BaseModel {
  producto_despensa_id: number;
  nombre: string;
  descripcion: string;
  precio_venta: number; // Precio de venta
  stock: number; // Nivel de stock
  activo: boolean; // Estado del producto (activo/inactivo)
  detalles_factura?: FacturaDetalle[];
}

// Proveedor - Exactamente como en el modelo Laravel y migración
export interface Proveedor extends BaseModel {
  proveedor_id: number;
  nombre: string;
  email?: string | null; // Nullable según la migración
  descripcion?: string | null; // Nullable según la migración
  telefono?: string | null; // Nullable según la migración
  deuda_pendiente: number; // Default 0 según la migración
  pagos?: PagoProveedor[];
}

// Pago Proveedor - Exactamente como en el modelo Laravel y migración
export interface PagoProveedor extends BaseModel {
  id_pago_proveedor: number; // Clave primaria personalizada según la migración
  proveedor_id: number;
  monto: number;
  fecha: string; // DateTime en la migración
  descripcion?: string | null; // Nullable según la migración
  proveedor?: Proveedor;
}

// Ingreso - Con lógica de negocio: referencia_id según el tipo
export interface Ingreso extends BaseModel {
  ingreso_id: number;
  fecha: string;
  tipo: 'lavado' | 'producto_automotriz' | 'producto_despensa'; // Enum exacto de la migración
  referencia_id?: number | null; // ID según el tipo: lavado_id, venta_producto_automotriz.id, venta_producto_despensa.id
  monto: number;
  descripcion?: string | null; // Nullable según la migración
  // Relaciones dinámicas según el tipo
  lavado?: Lavado; // Si tipo === 'lavado'
  venta_producto_automotriz?: VentaProductoAutomotriz; // Si tipo === 'producto_automotriz'
  venta_producto_despensa?: VentaProductoDespensa; // Si tipo === 'producto_despensa'
}

// Egreso - Con lógica de negocio: referencia_id según el tipo
export interface Egreso extends BaseModel {
  egreso_id: number;
  fecha: string;
  tipo: 'salario' | 'proveedor' | 'gasto_general'; // Enum exacto de la migración
  referencia_id?: number | null; // ID según el tipo: empleado_id, id_pago_proveedor, gasto_general_id
  monto: number;
  descripcion?: string | null; // Nullable según la migración
  // Relaciones dinámicas según el tipo
  empleado?: Empleado; // Si tipo === 'salario' (referencia_id = empleado_id)
  pago_proveedor?: PagoProveedor; // Si tipo === 'proveedor' (referencia_id = id_pago_proveedor)
  gasto_general?: GastoGeneral; // Si tipo === 'gasto_general' (referencia_id = gasto_general_id)
}

// Gasto General - Exactamente como en el modelo Laravel y migración
export interface GastoGeneral extends BaseModel {
  gasto_general_id: number;
  nombre: string;
  descripcion?: string | null; // Nullable según la migración
  monto: number;
  fecha: string;
}

// Factura - Exactamente como en el modelo Laravel y migración
export interface Factura extends BaseModel {
  factura_id: number;
  numero_factura: string; // Unique según la migración
  cliente_id: number;
  fecha: string; // Date según la migración
  descripcion?: string | null; // Nullable según la migración
  total: number;
  cliente?: Cliente;
  detalles?: FacturaDetalle[];
}

// Detalle de Factura - Exactamente como en el modelo Laravel y migración
export interface FacturaDetalle extends BaseModel {
  factura_detalle_id: number; // Clave primaria según la migración
  factura_id: number;
  lavado_id?: number | null; // Nullable según la migración
  venta_producto_automotriz_id?: number | null; // Nullable según la migración
  venta_producto_despensa_id?: number | null; // Nullable según la migración
  cantidad: number; // Default 1 según la migración
  precio_unitario: number;
  subtotal: number;
  factura?: Factura;
  lavado?: Lavado;
  venta_producto_automotriz?: VentaProductoAutomotriz;
  venta_producto_despensa?: VentaProductoDespensa;
}

// Venta Producto Automotriz - Exactamente como en el modelo Laravel y migración
export interface VentaProductoAutomotriz extends BaseModel {
  id: number; // Clave primaria default según la migración
  producto_id: number; // Referencia a producto_automotriz_id según la migración
  cliente_id?: number | null; // Nullable según la migración
  cantidad: number;
  precio_unitario: number;
  total: number;
  fecha: string; // DateTime según la migración
  producto_automotriz?: ProductoAutomotriz;
  cliente?: Cliente;
}

// Venta Producto Despensa - Exactamente como en el modelo Laravel y migración
export interface VentaProductoDespensa extends BaseModel {
  id: number; // Clave primaria default según la migración
  producto_id: number; // Referencia a producto_despensa_id según la migración
  cliente_id?: number | null; // Nullable según la migración
  cantidad: number;
  precio_unitario: number;
  total: number;
  fecha: string; // DateTime según la migración
  producto_despensa?: ProductoDespensa;
  cliente?: Cliente;
}

// Tipos para respuestas de API
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

// Filtros
export interface FilterOptions {
  search?: string;
  page?: number;
  per_page?: number;
  sort_by?: string;
  sort_direction?: 'asc' | 'desc';
  fecha_inicio?: string;
  fecha_fin?: string;
}

// Dashboard
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

// Autenticación
export interface LoginCredentials {
  email: string;
  password: string;
}

export interface AuthUser extends User {
  permissions?: string[];
  roles?: string[];
}

// Formularios - Exactamente como los campos del backend
export interface CreateEmpleadoForm {
  nombres: string;
  apellidos: string;
  telefono: string;
  cedula: string;
  tipo_salario: 'mensual' | 'diario' | 'quincenal' | 'semanal'; // Enum exacto
  salario: number;
}

export interface CreateVehiculoForm {
  cliente_id: number;
  tipo: 'moto' | 'camioneta' | 'auto_pequeno' | 'auto_mediano'; // Enum exacto
  matricula?: string; // Opcional
  descripcion?: string; // Opcional
}

export interface CreateLavadoForm {
  vehiculo_id: number;
  empleado_id: number;
  fecha: string;
  tipo_lavado: 'completo' | 'solo_fuera' | 'solo_por_dentro'; // Enum exacto
  precio: number;
  pulverizado: boolean;
}

// Request types - Exactamente como los campos del backend
export interface CreateEmpleadoRequest {
  nombres: string;
  apellidos: string;
  telefono: string;
  cedula: string;
  tipo_salario: 'mensual' | 'diario' | 'quincenal' | 'semanal'; // Enum exacto de la migración
  salario: number;
}

export interface UpdateEmpleadoRequest extends Partial<CreateEmpleadoRequest> {}

export interface CreateVehiculoRequest {
  cliente_id: number;
  tipo: 'moto' | 'camioneta' | 'auto_pequeno' | 'auto_mediano'; // Enum exacto de la migración
  matricula?: string; // Opcional
  descripcion?: string; // Opcional
}

export interface UpdateVehiculoRequest extends Partial<CreateVehiculoRequest> {}

export interface CreateLavadoRequest {
  vehiculo_id: number;
  empleado_id: number;
  fecha: string;
  tipo_lavado: 'completo' | 'solo_fuera' | 'solo_por_dentro'; // Enum exacto de la migración
  precio: number;
  pulverizado: boolean;
}

export interface UpdateLavadoRequest extends Partial<CreateLavadoRequest> {}

export interface CreateProductoAutomotrizRequest {
  codigo: string;
  nombre: string;
  descripcion?: string;
  precio_venta: number;
  stock?: number;
  activo?: boolean;
}

export interface UpdateProductoAutomotrizRequest extends Partial<CreateProductoAutomotrizRequest> {}

export interface CreateProductoDespensaRequest {
  nombre: string;
  descripcion?: string;
  precio_venta: number;
  stock?: number;
  activo?: boolean;
}

export interface UpdateProductoDespensaRequest extends Partial<CreateProductoDespensaRequest> {}

export interface CreateProveedorRequest {
  nombre: string;
  email?: string;
  descripcion?: string;
  telefono?: string;
  deuda_pendiente?: number;
}

export interface UpdateProveedorRequest extends Partial<CreateProveedorRequest> {}

export interface CreatePagoProveedorRequest {
  proveedor_id: number;
  monto: number;
  fecha: string;
  descripcion?: string;
}

export interface UpdatePagoProveedorRequest extends Partial<CreatePagoProveedorRequest> {}

// Selectores
export interface SelectOption {
  value: string | number;
  label: string;
  disabled?: boolean;
}

// Estados de carga
export interface LoadingState {
  loading: boolean;
  error: string | null;
}

// Configuración de tablas
export interface TableColumn {
  key: string;
  label: string;
  sortable?: boolean;
  width?: string;
  align?: 'left' | 'center' | 'right';
  format?: (value: any) => string;
}

export interface TableAction {
  key: string;
  label: string;
  icon?: string;
  color?: 'primary' | 'secondary' | 'danger';
  disabled?: (item: any) => boolean;
  visible?: (item: any) => boolean;
}

// Tipos específicos para lógica de negocio

// Formulario de vehículo con validación de matrícula según tipo
export interface CreateVehiculoFormWithValidation extends CreateVehiculoForm {
  // Si tipo === 'moto', matricula es opcional
  // Si tipo !== 'moto', matricula es requerida
  matricula: string | undefined;
}

// Tipos específicos para ingresos según el tipo
export interface CreateIngresoLavadoRequest {
  fecha: string;
  tipo: 'lavado';
  referencia_id: number; // lavado_id
  monto: number;
  descripcion?: string;
}

export interface CreateIngresoProductoAutomotrizRequest {
  fecha: string;
  tipo: 'producto_automotriz';
  referencia_id: number; // venta_producto_automotriz.id
  monto: number;
  descripcion?: string;
}

export interface CreateIngresoProductoDespensaRequest {
  fecha: string;
  tipo: 'producto_despensa';
  referencia_id: number; // venta_producto_despensa.id
  monto: number;
  descripcion?: string;
}

export type CreateIngresoRequest = 
  | CreateIngresoLavadoRequest 
  | CreateIngresoProductoAutomotrizRequest 
  | CreateIngresoProductoDespensaRequest;

// Tipos específicos para egresos según el tipo
export interface CreateEgresoSalarioRequest {
  fecha: string;
  tipo: 'salario';
  referencia_id: number; // empleado_id
  monto: number;
  descripcion?: string;
}

export interface CreateEgresoProveedorRequest {
  fecha: string;
  tipo: 'proveedor';
  referencia_id: number; // id_pago_proveedor
  monto: number;
  descripcion?: string;
}

export interface CreateEgresoGastoGeneralRequest {
  fecha: string;
  tipo: 'gasto_general';
  referencia_id: number; // gasto_general_id
  monto: number;
  descripcion?: string;
}

export type CreateEgresoRequest = 
  | CreateEgresoSalarioRequest 
  | CreateEgresoProveedorRequest 
  | CreateEgresoGastoGeneralRequest;

// Tipos para Gastos Generales
export interface CreateGastoGeneralRequest {
  nombre: string;
  descripcion?: string;
  monto: number;
  fecha: string;
}

export interface UpdateGastoGeneralRequest extends Partial<CreateGastoGeneralRequest> {}

// Tipos de Update para Ingresos y Egresos
export interface UpdateIngresoRequest extends Partial<Omit<CreateIngresoRequest, 'tipo'>> {
  tipo?: CreateIngresoRequest['tipo'];
}

export interface UpdateEgresoRequest extends Partial<Omit<CreateEgresoRequest, 'tipo'>> {
  tipo?: CreateEgresoRequest['tipo'];
}

// Helper types para validaciones de lógica de negocio

// Validador de matrícula según tipo de vehículo
export type VehiculoMatriculaValidation<T extends Vehiculo['tipo']> = T extends 'moto' 
  ? { matricula?: string | null } 
  : { matricula: string };

// Helper para determinar si un vehículo requiere matrícula
export const requiereMatricula = (tipo: Vehiculo['tipo']): boolean => {
  return tipo !== 'moto';
};

// Helper para obtener el nombre de la referencia según el tipo
export const getReferenciaTipo = (tipo: string): string => {
  const referencias: Record<string, string> = {
    // Ingresos
    'lavado': 'lavado_id',
    'producto_automotriz': 'venta_producto_automotriz_id',
    'producto_despensa': 'venta_producto_despensa_id',
    // Egresos
    'salario': 'empleado_id',
    'proveedor': 'id_pago_proveedor',
    'gasto_general': 'gasto_general_id'
  };
  return referencias[tipo] || 'referencia_id';
};

// Tipos para las requests de ventas
export interface CreateVentaAutomotrizRequest {
  producto_id: number;
  cliente_id?: number | null;
  cantidad: number;
  precio_unitario: number;
  fecha: string;
}

export interface UpdateVentaAutomotrizRequest extends Partial<CreateVentaAutomotrizRequest> {}

export interface CreateVentaDespensaRequest {
  producto_id: number;
  cliente_id?: number | null;
  cantidad: number;
  precio_unitario: number;
  fecha: string;
}

export interface UpdateVentaDespensaRequest extends Partial<CreateVentaDespensaRequest> {}

// Tipos para filtros de ventas
export interface VentaFilters {
  page?: number;
  per_page?: number;
  search?: string;
  tipo?: 'automotriz' | 'despensa';
  cliente_id?: number;
  producto_id?: number;
  fecha_inicio?: string;
  fecha_fin?: string;
}

// Tipo unificado para mostrar ventas en la tabla
export interface VentaUnificada {
  id: number;
  tipo: 'automotriz' | 'despensa';
  producto_nombre: string;
  cliente_nombre?: string;
  cantidad: number;
  precio_unitario: number;
  total: number;
  fecha: string;
  created_at?: string;
  updated_at?: string;
  deleted_at?: string | null;
  // Referencias originales para acciones
  original_data: VentaProductoAutomotriz | VentaProductoDespensa;
}

// Tipos para métricas de ventas
export interface VentaMetricas {
  total_ventas: number;
  total_ingresos: number;
  ventas_automotrices: {
    total: number;
    ingresos: number;
  };
  ventas_despensa: {
    total: number;
    ingresos: number;
  };
  producto_mas_vendido: {
    nombre: string;
    cantidad: number;
  } | null;
  ventas_por_mes: Array<{
    mes: string;
    cantidad: number;
    ingresos: number;
  }>;
}

// Tipo para opciones de productos en ventas
export interface ProductoVentaOption {
  id: number;
  nombre: string;
  codigo?: string;
  precio_venta: number;
  stock: number;
  tipo: 'automotriz' | 'despensa';
  activo: boolean;
}

// Tipos para filtros específicos de ventas automotrices y despensa
export interface VentaAutomotrizFilters extends VentaFilters {
  codigo_producto?: string;
}

export interface VentaDespensaFilters extends VentaFilters {
  stock_minimo?: number;
}

// ==========================================
// REPORTES TYPES
// ==========================================

// Tipos base para reportes
export interface ReporteDisponible {
  id: string;
  nombre: string;
  descripcion: string;
  categoria: string;
  requiere_fechas: boolean;
  formato_disponible: string[];
}

export interface ReporteRequest {
  fecha_inicio?: string;
  fecha_fin?: string;
  formato?: 'json' | 'pdf' | 'excel';
}

export interface ReporteMetricas {
  total: number;
  promedio: number;
  maximo: number;
  minimo: number;
  crecimiento?: number;
}

// Reporte de Ventas
export interface ReporteVentas {
  resumen: {
    total_ventas: number;
    total_unidades: number;
    ticket_promedio: number;
    crecimiento_periodo: number;
  };
  ventas_por_dia: Array<{
    fecha: string;
    total: number;
    cantidad: number;
  }>;
  productos_mas_vendidos: Array<{
    producto: string;
    cantidad: number;
    total: number;
  }>;
  ventas_por_categoria: Array<{
    categoria: string;
    total: number;
    porcentaje: number;
  }>;
}

// Reporte de Lavados
export interface ReporteLavados {
  resumen: {
    total_lavados: number;
    total_ingresos: number;
    lavado_promedio: number;
    crecimiento_periodo: number;
  };
  lavados_por_dia: Array<{
    fecha: string;
    cantidad: number;
    total: number;
  }>;
  tipos_mas_solicitados: Array<{
    tipo_lavado: string;
    cantidad: number;
    total: number;
  }>;
  empleados_performance: Array<{
    empleado: string;
    lavados_realizados: number;
    total_generado: number;
  }>;
}

// Reporte de Ingresos
export interface ReporteIngresos {
  resumen: {
    total_ingresos: number;
    promedio_diario: number;
    crecimiento: number;
  };
  ingresos_por_dia: Array<{
    fecha: string;
    total: number;
  }>;
  ingresos_por_fuente: Array<{
    fuente: string;
    total: number;
    porcentaje: number;
  }>;
  detalle_ingresos: Array<{
    fecha: string;
    concepto: string;
    monto: number;
    tipo: string;
  }>;
}

// Reporte de Egresos
export interface ReporteEgresos {
  resumen: {
    total_egresos: number;
    promedio_diario: number;
    crecimiento: number;
  };
  egresos_por_dia: Array<{
    fecha: string;
    total: number;
  }>;
  egresos_por_categoria: Array<{
    categoria: string;
    total: number;
    porcentaje: number;
  }>;
  detalle_egresos: Array<{
    fecha: string;
    concepto: string;
    monto: number;
    categoria: string;
  }>;
}

// Reporte de Facturas
export interface ReporteFacturas {
  resumen: {
    total_facturas: number;
    total_facturado: number;
    factura_promedio: number;
    crecimiento: number;
  };
  facturas_por_dia: Array<{
    fecha: string;
    cantidad: number;
    total: number;
  }>;
  clientes_top: Array<{
    cliente: string;
    facturas: number;
    total: number;
  }>;
  detalle_facturas: Array<{
    numero_factura: string;
    fecha: string;
    cliente: string;
    total: number;
  }>;
}

// Reporte de Empleados
export interface ReporteEmpleados {
  resumen: {
    total_empleados: number;
    promedio_performance: number;
  };
  performance_empleados: Array<{
    empleado: string;
    lavados_realizados: number;
    ventas_realizadas: number;
    total_generado: number;
    calificacion: number;
  }>;
  empleados_por_mes: Array<{
    mes: string;
    empleados_activos: number;
  }>;
}

// Reporte de Productos
export interface ReporteProductos {
  resumen: {
    total_productos: number;
    productos_agotados: number;
    valor_inventario: number;
  };
  productos_mas_vendidos: Array<{
    producto: string;
    categoria: string;
    cantidad_vendida: number;
    stock_actual: number;
    valor_total: number;
  }>;
  productos_bajo_stock: Array<{
    producto: string;
    stock_actual: number;
    stock_minimo: number;
    categoria: string;
  }>;
  rotacion_inventario: Array<{
    producto: string;
    rotacion: number;
    dias_promedio: number;
  }>;
}

// Reporte de Clientes
export interface ReporteClientes {
  resumen: {
    total_clientes: number;
    clientes_activos: number;
    ticket_promedio: number;
    frecuencia_promedio: number;
  };
  clientes_top: Array<{
    cliente: string;
    total_gastado: number;
    visitas: number;
    ultima_visita: string;
  }>;
  clientes_nuevos: Array<{
    mes: string;
    nuevos_clientes: number;
  }>;
  segmentacion: Array<{
    segmento: string;
    cantidad: number;
    porcentaje: number;
  }>;
}

// Reporte Financiero
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

// Reporte de Balance
export interface ReporteBalance {
  activos: {
    efectivo: number;
    inventario: number;
    cuentas_por_cobrar: number;
    total_activos: number;
  };
  pasivos: {
    cuentas_por_pagar: number;
    total_pasivos: number;
  };
  patrimonio: {
    capital: number;
    utilidades_retenidas: number;
    total_patrimonio: number;
  };
  ratios: {
    liquidez: number;
    rentabilidad: number;
    endeudamiento: number;
  };
}

// Reporte Completo
export interface ReporteCompleto {
  periodo: {
    fecha_inicio: string;
    fecha_fin: string;
  };
  ventas: ReporteVentas;
  lavados: ReporteLavados;
  financiero: ReporteFinanciero;
  clientes: ReporteClientes;
  empleados: ReporteEmpleados;
  productos: ReporteProductos;
}
