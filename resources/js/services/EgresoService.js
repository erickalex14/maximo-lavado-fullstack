import api from './api';

/**
 * Servicio para gestión de egresos
 * Consume la API real del backend sin datos de ejemplo
 * Endpoints disponibles según routes/api.php:
 * - GET /egresos - Obtener egresos con paginación
 * - POST /egresos - Crear nuevo egreso
 * - GET /egresos/{id} - Obtener egreso por ID
 * - PUT /egresos/{id} - Actualizar egreso
 * - DELETE /egresos/{id} - Eliminar egreso
 * - GET /egresos/metricas - Obtener métricas de egresos
 */
class EgresoServiceClass {
  /**
   * Obtener todos los egresos con paginación
   * @param {object} params - Parámetros de consulta (página, filtros, etc.)
   * @returns {Promise} Respuesta de la API
   */
  async getAll(params = {}) {
    const response = await api.get('/egresos', { params });
    return response.data;
  }

  /**
   * Obtener un egreso por ID
   * @param {number} id - ID del egreso
   * @returns {Promise} Respuesta de la API
   */
  async getById(id) {
    const response = await api.get(`/egresos/${id}`);
    return response.data;
  }

  /**
   * Crear un nuevo egreso
   * @param {object} data - Datos del egreso a crear
   * @returns {Promise} Respuesta de la API
   */
  async create(data) {
    const response = await api.post('/egresos', data);
    return response.data;
  }

  /**
   * Actualizar un egreso
   * @param {number} id - ID del egreso
   * @param {object} data - Datos actualizados del egreso
   * @returns {Promise} Respuesta de la API
   */
  async update(id, data) {
    const response = await api.put(`/egresos/${id}`, data);
    return response.data;
  }

  /**
   * Eliminar un egreso
   * @param {number} id - ID del egreso
   * @returns {Promise} Respuesta de la API
   */
  async delete(id) {
    const response = await api.delete(`/egresos/${id}`);
    return response.data;
  }

  /**
   * Obtener métricas de egresos
   * @param {object} params - Parámetros de consulta (fechas, filtros, etc.)
   * @returns {Promise} Respuesta de la API
   */
  async getMetricas(params = {}) {
    const response = await api.get('/egresos/metricas', { params });
    return response.data;
  }
}

// Crear instancia única del servicio
export const egresoService = new EgresoServiceClass();
export default egresoService;
