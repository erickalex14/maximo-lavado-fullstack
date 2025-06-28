import api from './api';

/**
 * Servicio para gestión de ventas
 * Consume la API real del backend sin datos de ejemplo
 * Endpoints disponibles según routes/api.php:
 * - GET /ventas - Obtener ventas con paginación
 * - POST /ventas - Crear nueva venta
 * - GET /ventas/metricas - Obtener métricas de ventas
 * - GET /ventas/productos-disponibles - Obtener productos disponibles para venta
 * - GET /ventas/clientes - Obtener clientes para selección
 */
class VentaServiceClass {
  /**
   * Obtener todas las ventas con paginación
   * @param {object} params - Parámetros de consulta (página, filtros, etc.)
   * @returns {Promise} Respuesta de la API
   */
  async getAll(params = {}) {
    const response = await api.get('/ventas', { params });
    return response.data;
  }

  /**
   * Crear una nueva venta
   * @param {object} data - Datos de la venta a crear
   * @returns {Promise} Respuesta de la API
   */
  async create(data) {
    const response = await api.post('/ventas', data);
    return response.data;
  }

  /**
   * Obtener métricas de ventas
   * @param {object} params - Parámetros de consulta (fechas, filtros, etc.)
   * @returns {Promise} Respuesta de la API
   */
  async getMetricas(params = {}) {
    const response = await api.get('/ventas/metricas', { params });
    return response.data;
  }

  /**
   * Obtener productos disponibles para venta
   * @param {object} params - Parámetros de consulta
   * @returns {Promise} Respuesta de la API
   */
  async getProductosDisponibles(params = {}) {
    const response = await api.get('/ventas/productos-disponibles', { params });
    return response.data;
  }

  /**
   * Obtener clientes disponibles para selección
   * @param {object} params - Parámetros de consulta
   * @returns {Promise} Respuesta de la API
   */
  async getClientes(params = {}) {
    const response = await api.get('/ventas/clientes', { params });
    return response.data;
  }
}

// Crear instancia única del servicio
export const ventaService = new VentaServiceClass();
export default ventaService;
