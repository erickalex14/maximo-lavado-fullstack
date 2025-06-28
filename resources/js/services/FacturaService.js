import apiClient from './api';

export class FacturaService {
  /**
   * Obtener todas las facturas
   */
  static async getAll(params = {}) {
    const response = await apiClient.get('/facturas', { params });
    return response.data;
  }

  /**
   * Obtener una factura por ID
   */
  static async getById(id) {
    const response = await apiClient.get(`/facturas/${id}`);
    return response.data;
  }

  /**
   * Crear una nueva factura
   */
  static async create(data) {
    const response = await apiClient.post('/facturas', data);
    return response.data;
  }

  /**
   * Actualizar una factura
   */
  static async update(id, data) {
    const response = await apiClient.put(`/facturas/${id}`, data);
    return response.data;
  }

  /**
   * Eliminar una factura
   */
  static async delete(id) {
    const response = await apiClient.delete(`/facturas/${id}`);
    return response.data;
  }

  /**
   * Generar factura desde un lavado
   */
  static async generarDesdeLavado(lavadoId, data = {}) {
    const response = await apiClient.post(`/facturas/generar-desde-lavado/${lavadoId}`, data);
    return response.data;
  }

  /**
   * Marcar factura como pagada
   */
  static async marcarComoPagada(id, data = {}) {
    const response = await apiClient.patch(`/facturas/${id}/pagar`, data);
    return response.data;
  }

  /**
   * Obtener PDF de la factura
   */
  static async getPDF(id) {
    const response = await apiClient.get(`/facturas/${id}/pdf`, {
      responseType: 'blob'
    });
    return response.data;
  }

  /**
   * Enviar factura por email
   */
  static async enviarPorEmail(id, data = {}) {
    const response = await apiClient.post(`/facturas/${id}/enviar-email`, data);
    return response.data;
  }

  /**
   * Obtener detalles de una factura
   */
  static async getDetalles(id) {
    const response = await apiClient.get(`/facturas/${id}/detalles`);
    return response.data;
  }

  /**
   * Obtener resumen de facturación
   */
  static async getResumen(params = {}) {
    const response = await apiClient.get('/facturas/resumen', { params });
    return response.data;
  }
}

export default FacturaService;
