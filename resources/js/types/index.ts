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

// Cliente
export interface Cliente extends BaseModel {
  cliente_id: number;
  nombre: string;
  telefono: string;
  email: string;
  direccion: string;
  cedula: string;
  vehiculos?: Vehiculo[];
  facturas?: Factura[];
}

// Empleado
export interface Empleado extends BaseModel {
  empleado_id: number;
  nombres: string;
  apellidos: string;
  telefono: string;
  cedula: string;
  tipo_salario: string;
  salario: number;
  lavados?: Lavado[];
}

// Vehículo
export interface Vehiculo extends BaseModel {
  vehiculo_id: number;
  cliente_id: number;
  tipo_vehiculo: 'moto' | 'auto' | 'camioneta';
  marca: string;
  modelo: string;
  color: string;
  matricula: string;
  cliente?: Cliente;
  lavados?: Lavado[];
}

// Lavado
export interface Lavado extends BaseModel {
  lavado_id: number;
  vehiculo_id: number;
  empleado_id: number;
  fecha: string;
  tipo_lavado: 'completo' | 'solo_fuera' | 'solo_por_dentro';
  precio: number;
  pulverizado: boolean;
  vehiculo?: Vehiculo;
  empleado?: Empleado;
  detalles_factura?: FacturaDetalle[];
}

// Producto Automotriz
export interface ProductoAutomotriz extends BaseModel {
  producto_automotriz_id: number;
  nombre: string;
  descripcion: string;
  precio_compra: number;
  precio_venta: number;
  stock: number;
  stock_minimo: number;
  proveedor_id: number;
  proveedor?: Proveedor;
}

// Producto Despensa
export interface ProductoDespensa extends BaseModel {
  producto_despensa_id: number;
  nombre: string;
  descripcion: string;
  precio_compra: number;
  precio_venta: number;
  stock: number;
  stock_minimo: number;
  proveedor_id: number;
  proveedor?: Proveedor;
}

// Proveedor
export interface Proveedor extends BaseModel {
  proveedor_id: number;
  nombre: string;
  telefono: string;
  email: string;
  direccion: string;
  ruc: string;
  productos_automotrices?: ProductoAutomotriz[];
  productos_despensa?: ProductoDespensa[];
  pagos?: PagoProveedor[];
}

// Pago Proveedor
export interface PagoProveedor extends BaseModel {
  pago_proveedor_id: number;
  proveedor_id: number;
  monto: number;
  fecha_pago: string;
  descripcion: string;
  proveedor?: Proveedor;
}

// Ingreso
export interface Ingreso extends BaseModel {
  ingreso_id: number;
  descripcion: string;
  monto: number;
  fecha: string;
  categoria: string;
}

// Egreso
export interface Egreso extends BaseModel {
  egreso_id: number;
  descripcion: string;
  monto: number;
  fecha: string;
  categoria: string;
}

// Gasto General
export interface GastoGeneral extends BaseModel {
  gasto_general_id: number;
  descripcion: string;
  monto: number;
  fecha: string;
  categoria: string;
}

// Factura
export interface Factura extends BaseModel {
  factura_id: number;
  cliente_id: number;
  numero_factura: string;
  fecha_emision: string;
  subtotal: number;
  impuestos: number;
  total: number;
  estado: 'pendiente' | 'pagada' | 'cancelada';
  cliente?: Cliente;
  detalles?: FacturaDetalle[];
}

// Detalle de Factura
export interface FacturaDetalle extends BaseModel {
  detalle_id: number;
  factura_id: number;
  lavado_id?: number | null;
  producto_automotriz_id?: number | null;
  producto_despensa_id?: number | null;
  descripcion: string;
  cantidad: number;
  precio_unitario: number;
  subtotal: number;
  factura?: Factura;
  lavado?: Lavado;
  producto_automotriz?: ProductoAutomotriz;
  producto_despensa?: ProductoDespensa;
}

// Venta Producto Automotriz
export interface VentaProductoAutomotriz extends BaseModel {
  venta_producto_automotriz_id: number;
  producto_automotriz_id: number;
  cliente_id: number;
  cantidad: number;
  precio_unitario: number;
  total: number;
  fecha_venta: string;
  producto_automotriz?: ProductoAutomotriz;
  cliente?: Cliente;
}

// Venta Producto Despensa
export interface VentaProductoDespensa extends BaseModel {
  venta_producto_despensa_id: number;
  producto_despensa_id: number;
  cliente_id: number;
  cantidad: number;
  precio_unitario: number;
  total: number;
  fecha_venta: string;
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

// Formularios
export interface CreateClienteForm {
  nombre: string;
  telefono: string;
  email: string;
  direccion: string;
  cedula: string;
}

export interface CreateEmpleadoForm {
  nombres: string;
  apellidos: string;
  telefono: string;
  cedula: string;
  tipo_salario: string;
  salario: number;
}

export interface CreateVehiculoForm {
  cliente_id: number;
  tipo_vehiculo: 'moto' | 'auto' | 'camioneta';
  marca: string;
  modelo: string;
  color: string;
  matricula: string;
}

export interface CreateLavadoForm {
  vehiculo_id: number;
  empleado_id: number;
  fecha: string;
  tipo_lavado: 'completo' | 'solo_fuera' | 'solo_por_dentro';
  precio: number;
  pulverizado: boolean;
}

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
