import apiClient from './api';

export class EmpleadoService {
  /**
   * Obtener todos los empleados con paginación
   */
  static async getEmpleados(params = {}) {
    const response = await apiClient.get('/empleados', { params });
    return response.data;
  }

  /**
   * Obtener empleado por ID
   */
  static async getEmpleado(id) {
    const response = await apiClient.get(`/empleados/${id}`);
    return response.data;
  }

  /**
   * Crear nuevo empleado
   */
  static async createEmpleado(data) {
    const response = await apiClient.post('/empleados', data);
    return response.data;
  }

  /**
   * Actualizar empleado
   */
  static async updateEmpleado(id, data) {
    const response = await apiClient.put(`/empleados/${id}`, data);
    return response.data;
  }

  /**
   * Eliminar empleado
   */
  static async deleteEmpleado(id) {
    const response = await apiClient.delete(`/empleados/${id}`);
    return response.data;
  }

  /**
   * Obtener lavados por empleado y día
   */
  static async getLavadosPorDia(empleadoId, fecha) {
    const response = await apiClient.get(`/empleados/${empleadoId}/lavados/dia/${fecha}`);
    return response.data;
  }

  /**
   * Obtener lavados por empleado y semana
   */
  static async getLavadosPorSemana(empleadoId, fecha) {
    const response = await apiClient.get(`/empleados/${empleadoId}/lavados/semana/${fecha}`);
    return response.data;
  }

  /**
   * Obtener lavados por empleado y mes
   */
  static async getLavadosPorMes(empleadoId, anio, mes) {
    const response = await apiClient.get(`/empleados/${empleadoId}/lavados/mes/${anio}/${mes}`);
    return response.data;
  }
}

export default EmpleadoService;
