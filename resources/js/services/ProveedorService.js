import api from './api';

/**
 * Servicio consolidado para gestión de proveedores y sus pagos
 * Consume la API real del backend consolidada en ProveedorController
 * 
 * Toda la gestión de proveedores y pagos está centralizada en ProveedorController
 * 
 * Endpoints consolidados activos:
 * - GET /proveedores - Obtener proveedores con paginación
 * - POST /proveedores - Crear nuevo proveedor
 * - GET /proveedores/{id} - Obtener proveedor por ID
 * - PUT /proveedores/{id} - Actualizar proveedor
 * - DELETE /proveedores/{id} - Eliminar proveedor
 * - GET /proveedores/{id}/deuda - Ver deuda del proveedor
 * - GET /proveedores/{id}/pagos - Historial de pagos de un proveedor
 * - GET /proveedores/pagos - Todos los pagos de todos los proveedores (con filtros)
 * - GET /proveedores/pagos/metricas - Métricas de pagos
 * - GET /proveedores/pagos/{pagoId} - Detalle de un pago específico
 * - PUT /proveedores/pagos/{pagoId} - Actualizar un pago específico
 * - DELETE /proveedores/pagos/{pagoId} - Eliminar un pago específico
 * - POST /proveedores/pagos - Crear pago con transacción completa
 */
class ProveedorServiceClass {
  /**
   * Obtener todos los proveedores con paginación
   * @param {object} params - Parámetros de consulta (página, filtros, etc.)
   * @returns {Promise} Respuesta de la API
   */
  async getAll(params = {}) {
    const response = await api.get('/proveedores', { params });
    return response.data;
  }

  /**
   * Obtener un proveedor por ID
   * @param {number} id - ID del proveedor
   * @returns {Promise} Respuesta de la API
   */
  async getById(id) {
    const response = await api.get(`/proveedores/${id}`);
    return response.data;
  }

  /**
   * Crear un nuevo proveedor
   * @param {object} data - Datos del proveedor a crear
   * @returns {Promise} Respuesta de la API
   */
  async create(data) {
    const response = await api.post('/proveedores', data);
    return response.data;
  }

  /**
   * Actualizar un proveedor
   * @param {number} id - ID del proveedor
   * @param {object} data - Datos actualizados del proveedor
   * @returns {Promise} Respuesta de la API
   */
  async update(id, data) {
    const response = await api.put(`/proveedores/${id}`, data);
    return response.data;
  }

  /**
   * Eliminar un proveedor
   * @param {number} id - ID del proveedor
   * @returns {Promise} Respuesta de la API
   */
  async delete(id) {
    const response = await api.delete(`/proveedores/${id}`);
    return response.data;
  }

  /**
   * Ver deuda de un proveedor
   * @param {number} id - ID del proveedor
   * @returns {Promise} Respuesta de la API
   */
  async verDeuda(id) {
    const response = await api.get(`/proveedores/${id}/deuda`);
    return response.data;
  }

  /**
   * Obtener historial de pagos de un proveedor específico
   * @param {number} id - ID del proveedor
   * @param {object} params - Parámetros de consulta
   * @returns {Promise} Respuesta de la API
   */
  async getPagosProveedor(id, params = {}) {
    const response = await api.get(`/proveedores/${id}/pagos`, { params });
    return response.data;
  }

  // ==========================================================
  // MÉTODOS PARA GESTIÓN CONSOLIDADA DE PAGOS
  // (Nuevas rutas consolidadas en ProveedorController)
  // ==========================================================

  /**
   * Obtener todos los pagos de todos los proveedores
   * Ruta consolidada: GET /proveedores/pagos
   * @param {object} params - Parámetros de consulta (fechas, proveedor_id, etc.)
   * @returns {Promise} Respuesta de la API
   */
  async getTodosPagos(params = {}) {
    const response = await api.get('/proveedores/pagos', { params });
    return response.data;
  }

  /**
   * Obtener un pago específico por ID
   * Ruta consolidada: GET /proveedores/pagos/{id}
   * @param {number} id - ID del pago
   * @returns {Promise} Respuesta de la API
   */
  async getPagoById(id) {
    const response = await api.get(`/proveedores/pagos/${id}`);
    return response.data;
  }

  /**
   * Crear un pago con transacción completa (pago + actualizar deuda + registrar egreso)
   * Ruta consolidada: POST /proveedores/pagos
   * @param {object} data - Datos del pago (debe incluir proveedor_id)
   * @returns {Promise} Respuesta de la API
   */
  async crearPago(data) {
    const response = await api.post('/proveedores/pagos', data);
    return response.data;
  }

  /**
   * Actualizar un pago específico
   * Ruta consolidada: PUT /proveedores/pagos/{id}
   * @param {number} id - ID del pago
   * @param {object} data - Datos actualizados del pago
   * @returns {Promise} Respuesta de la API
   */
  async actualizarPago(id, data) {
    const response = await api.put(`/proveedores/pagos/${id}`, data);
    return response.data;
  }

  /**
   * Eliminar un pago específico
   * Ruta consolidada: DELETE /proveedores/pagos/{id}
   * @param {number} id - ID del pago
   * @returns {Promise} Respuesta de la API
   */
  async eliminarPago(id) {
    const response = await api.delete(`/proveedores/pagos/${id}`);
    return response.data;
  }

  /**
   * Obtener métricas de pagos a proveedores
   * Ruta consolidada: GET /proveedores/pagos/metricas
   * @param {object} params - Parámetros de consulta (fechas, filtros, etc.)
   * @returns {Promise} Respuesta de la API
   */
  async getMetricasPagos(params = {}) {
    const response = await api.get('/proveedores/pagos/metricas', { params });
    return response.data;
  }
}

// Crear instancia única del servicio
export const proveedorService = new ProveedorServiceClass();
export default proveedorService;
