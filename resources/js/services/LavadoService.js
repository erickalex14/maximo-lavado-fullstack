import BaseService from './BaseService';

/**
 * Servicio para gestión de lavados
 * Extiende BaseService para operaciones CRUD básicas
 */
class LavadoServiceClass extends BaseService {
  constructor() {
    super('/lavados');
  }

  /**
   * Obtener lavados por vehículo
   * @param {number} vehiculoId - ID del vehículo
   */
  async getByVehiculo(vehiculoId) {
    return this.customAction('vehiculo', vehiculoId, null, 'GET');
  }

  /**
   * Obtener lavados por cliente
   * @param {number} clienteId - ID del cliente
   */
  async getByCliente(clienteId) {
    return this.customAction('cliente', clienteId, null, 'GET');
  }

  /**
   * Obtener lavados por empleado
   * @param {number} empleadoId - ID del empleado
   */
  async getByEmpleado(empleadoId) {
    return this.customAction('empleado', empleadoId, null, 'GET');
  }

  /**
   * Iniciar nuevo lavado
   * @param {object} data - Datos del lavado
   */
  async iniciar(data) {
    return this.customAction('iniciar', null, data);
  }

  /**
   * Finalizar lavado
   * @param {number} id - ID del lavado
   * @param {object} data - Datos de finalización
   */
  async finalizar(id, data) {
    return this.customAction('finalizar', id, data);
  }

  /**
   * Cancelar lavado
   * @param {number} id - ID del lavado
   * @param {string} motivo - Motivo de cancelación
   */
  async cancelar(id, motivo) {
    return this.customAction('cancelar', id, { motivo });
  }

  /**
   * Obtener lavados del día
   */
  async getDelDia() {
    return this.customAction('del-dia', null, null, 'GET');
  }

  /**
   * Obtener lavados pendientes
   */
  async getPendientes() {
    return this.customAction('pendientes', null, null, 'GET');
  }

  // =======================================
  // MÉTODOS DE COMPATIBILIDAD (deprecated)
  // =======================================
  
  /**
   * @deprecated Usar getAll(params) en su lugar
   */
  static async getLavados(params = {}) {
    return lavadoService.getAll(params);
  }

  /**
   * @deprecated Usar getById(id) en su lugar
   */
  static async getLavado(id) {
    return lavadoService.getById(id);
  }

  /**
   * @deprecated Usar create(data) en su lugar
   */
  static async createLavado(data) {
    return lavadoService.create(data);
  }

  /**
   * @deprecated Usar update(id, data) en su lugar
   */
  static async updateLavado(id, data) {
    return lavadoService.update(id, data);
  }

  /**
   * @deprecated Usar delete(id) en su lugar
   */
  static async deleteLavado(id) {
    return lavadoService.delete(id);
  }
}

// Exportar instancia singleton
export const lavadoService = new LavadoServiceClass();

// Para compatibilidad con imports existentes
export const LavadoService = LavadoServiceClass;
export default lavadoService;
