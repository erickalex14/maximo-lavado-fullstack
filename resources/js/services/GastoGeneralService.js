import api from './api';

/**
 * Servicio para gestión de gastos generales
 * Consume la API real del backend sin datos de ejemplo
 * Endpoints disponibles según routes/api.php:
 * - GET /gastos-generales - Obtener gastos generales con paginación
 * - POST /gastos-generales - Crear nuevo gasto general
 * - GET /gastos-generales/{id} - Obtener gasto general por ID
 * - PUT /gastos-generales/{id} - Actualizar gasto general
 * - DELETE /gastos-generales/{id} - Eliminar gasto general
 * - GET /gastos-generales/metricas - Obtener métricas de gastos generales
 */
class GastoGeneralServiceClass {
  /**
   * Obtener todos los gastos generales con paginación
   * @param {object} params - Parámetros de consulta (página, filtros, etc.)
   * @returns {Promise} Respuesta de la API
   */
  async getAll(params = {}) {
    const response = await api.get('/gastos-generales', { params });
    return response.data;
  }

  /**
   * Obtener un gasto general por ID
   * @param {number} id - ID del gasto general
   * @returns {Promise} Respuesta de la API
   */
  async getById(id) {
    const response = await api.get(`/gastos-generales/${id}`);
    return response.data;
  }

  /**
   * Crear un nuevo gasto general
   * @param {object} data - Datos del gasto general a crear
   * @returns {Promise} Respuesta de la API
   */
  async create(data) {
    const response = await api.post('/gastos-generales', data);
    return response.data;
  }

  /**
   * Actualizar un gasto general
   * @param {number} id - ID del gasto general
   * @param {object} data - Datos actualizados del gasto general
   * @returns {Promise} Respuesta de la API
   */
  async update(id, data) {
    const response = await api.put(`/gastos-generales/${id}`, data);
    return response.data;
  }

  /**
   * Eliminar un gasto general
   * @param {number} id - ID del gasto general
   * @returns {Promise} Respuesta de la API
   */
  async delete(id) {
    const response = await api.delete(`/gastos-generales/${id}`);
    return response.data;
  }

  /**
   * Obtener métricas de gastos generales
   * @param {object} params - Parámetros de consulta (fechas, filtros, etc.)
   * @returns {Promise} Respuesta de la API
   */
  async getMetricas(params = {}) {
    const response = await api.get('/gastos-generales/metricas', { params });
    return response.data;
  }
}

// Crear instancia única del servicio
export const gastoGeneralService = new GastoGeneralServiceClass();
export default gastoGeneralService;
