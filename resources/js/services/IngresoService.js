import api from './api';

/**
 * Servicio para gestión de ingresos
 * Consume la API real del backend sin datos de ejemplo
 * Endpoints disponibles según routes/api.php:
 * - GET /ingresos - Obtener ingresos con paginación
 * - POST /ingresos - Crear nuevo ingreso
 * - GET /ingresos/{id} - Obtener ingreso por ID
 * - PUT /ingresos/{id} - Actualizar ingreso
 * - DELETE /ingresos/{id} - Eliminar ingreso
 * - GET /ingresos/metricas - Obtener métricas de ingresos
 */
class IngresoServiceClass {
  /**
   * Obtener todos los ingresos con paginación
   * @param {object} params - Parámetros de consulta (página, filtros, etc.)
   * @returns {Promise} Respuesta de la API
   */
  async getAll(params = {}) {
    const response = await api.get('/ingresos', { params });
    return response.data;
  }

  /**
   * Obtener un ingreso por ID
   * @param {number} id - ID del ingreso
   * @returns {Promise} Respuesta de la API
   */
  async getById(id) {
    const response = await api.get(`/ingresos/${id}`);
    return response.data;
  }

  /**
   * Crear un nuevo ingreso
   * @param {object} data - Datos del ingreso a crear
   * @returns {Promise} Respuesta de la API
   */
  async create(data) {
    const response = await api.post('/ingresos', data);
    return response.data;
  }

  /**
   * Actualizar un ingreso
   * @param {number} id - ID del ingreso
   * @param {object} data - Datos actualizados del ingreso
   * @returns {Promise} Respuesta de la API
   */
  async update(id, data) {
    const response = await api.put(`/ingresos/${id}`, data);
    return response.data;
  }

  /**
   * Eliminar un ingreso
   * @param {number} id - ID del ingreso
   * @returns {Promise} Respuesta de la API
   */
  async delete(id) {
    const response = await api.delete(`/ingresos/${id}`);
    return response.data;
  }

  /**
   * Obtener métricas de ingresos
   * @param {object} params - Parámetros de consulta (fechas, filtros, etc.)
   * @returns {Promise} Respuesta de la API
   */
  async getMetricas(params = {}) {
    const response = await api.get('/ingresos/metricas', { params });
    return response.data;
  }
}

// Crear instancia única del servicio
export const ingresoService = new IngresoServiceClass();
export default ingresoService;
