import api from './api';

/**
 * Servicio para gestión de facturas
 * Consume la API real del backend sin datos de ejemplo
 * Endpoints disponibles según routes/api.php:
 * - GET /facturas - Obtener facturas con paginación
 * - POST /facturas - Crear nueva factura
 * - GET /facturas/{id} - Obtener factura por ID
 * - PUT /facturas/{id} - Actualizar factura
 * - DELETE /facturas/{id} - Eliminar factura
 * - GET /facturas/numero/{numeroFactura} - Buscar por número
 * - GET /facturas/metricas - Obtener métricas de facturas
 */
class FacturaServiceClass {
  /**
   * Obtener todas las facturas con paginación
   * @param {object} params - Parámetros de consulta (página, filtros, etc.)
   * @returns {Promise} Respuesta de la API
   */
  async getAll(params = {}) {
    const response = await api.get('/facturas', { params });
    return response.data;
  }

  /**
   * Obtener una factura por ID
   * @param {number} id - ID de la factura
   * @returns {Promise} Respuesta de la API
   */
  async getById(id) {
    const response = await api.get(`/facturas/${id}`);
    return response.data;
  }

  /**
   * Crear una nueva factura
   * @param {object} data - Datos de la factura a crear
   * @returns {Promise} Respuesta de la API
   */
  async create(data) {
    const response = await api.post('/facturas', data);
    return response.data;
  }

  /**
   * Actualizar una factura
   * @param {number} id - ID de la factura
   * @param {object} data - Datos actualizados de la factura
   * @returns {Promise} Respuesta de la API
   */
  async update(id, data) {
    const response = await api.put(`/facturas/${id}`, data);
    return response.data;
  }

  /**
   * Eliminar una factura
   * @param {number} id - ID de la factura
   * @returns {Promise} Respuesta de la API
   */
  async delete(id) {
    const response = await api.delete(`/facturas/${id}`);
    return response.data;
  }

  /**
   * Buscar factura por número
   * @param {string} numeroFactura - Número de la factura
   * @returns {Promise} Respuesta de la API
   */
  async findByNumero(numeroFactura) {
    const response = await api.get(`/facturas/numero/${numeroFactura}`);
    return response.data;
  }

  /**
   * Obtener métricas de facturas
   * @param {object} params - Parámetros de consulta (fechas, filtros, etc.)
   * @returns {Promise} Respuesta de la API
   */
  async getMetricas(params = {}) {
    const response = await api.get('/facturas/metricas', { params });
    return response.data;
  }
}

// Crear instancia única del servicio
export const facturaService = new FacturaServiceClass();
export default facturaService;
