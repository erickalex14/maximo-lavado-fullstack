import api from './api';

/**
 * Servicio completo para gestión de ventas (Automotrices y Despensa)
 * Consume la API consolidada del backend VentaController
 * 
 * Aplica principios SOLID:
 * - Single Responsibility: Solo maneja operaciones de ventas
 * - Open/Closed: Extensible para nuevos tipos de venta
 * - Liskov Substitution: Métodos consistentes para todos los tipos
 * - Interface Segregation: Métodos específicos por funcionalidad
 * - Dependency Inversion: Depende de la abstracción API
 * 
 * Endpoints disponibles según routes/api.php:
 * 
 * GENERALES:
 * - GET /ventas - Obtener todas las ventas con filtros
 * - GET /ventas/metricas - Obtener métricas generales
 * - GET /ventas/productos-disponibles - Productos disponibles
 * - GET /ventas/clientes - Clientes disponibles
 * 
 * VENTAS AUTOMOTRICES:
 * - GET /ventas/automotrices - Listar ventas automotrices
 * - POST /ventas/automotrices - Crear venta automotriz
 * - GET /ventas/automotrices/{id} - Obtener venta automotriz específica
 * - PUT /ventas/automotrices/{id} - Actualizar venta automotriz
 * - DELETE /ventas/automotrices/{id} - Eliminar venta automotriz
 * - GET /ventas/automotrices/metricas - Métricas de ventas automotrices
 * 
 * VENTAS DESPENSA:
 * - GET /ventas/despensa - Listar ventas de despensa
 * - POST /ventas/despensa - Crear venta de despensa
 * - GET /ventas/despensa/{id} - Obtener venta de despensa específica
 * - PUT /ventas/despensa/{id} - Actualizar venta de despensa
 * - DELETE /ventas/despensa/{id} - Eliminar venta de despensa
 * - GET /ventas/despensa/metricas - Métricas de ventas de despensa
 */
class VentaServiceClass {
  // =================================================================
  // MÉTODOS GENERALES DE VENTAS
  // =================================================================

  /**
   * Obtener todas las ventas con filtros opcionales
   * @param {object} params - Parámetros de consulta (fecha_inicio, fecha_fin, cliente_id, etc.)
   * @returns {Promise} Respuesta de la API
   */
  async getAll(params = {}) {
    return this.makeApiCall('/ventas', 'GET', null, params);
  }

  /**
   * Obtener métricas generales de ventas
   * @param {object} params - Parámetros de consulta (fechas, filtros, etc.)
   * @returns {Promise} Respuesta de la API
   */
  async getMetricas(params = {}) {
    return this.makeApiCall('/ventas/metricas', 'GET', null, params);
  }

  /**
   * Obtener productos disponibles para venta
   * @param {object} params - Parámetros de consulta
   * @returns {Promise} Respuesta de la API
   */
  async getProductosDisponibles(params = {}) {
    return this.makeApiCall('/ventas/productos-disponibles', 'GET', null, params);
  }

  /**
   * Obtener clientes disponibles para selección
   * @param {object} params - Parámetros de consulta
   * @returns {Promise} Respuesta de la API
   */
  async getClientes(params = {}) {
    return this.makeApiCall('/ventas/clientes', 'GET', null, params);
  }

  // =================================================================
  // MÉTODOS PARA VENTAS AUTOMOTRICES
  // =================================================================

  /**
   * Obtener todas las ventas de productos automotrices
   * @param {object} params - Parámetros de consulta (fecha_inicio, fecha_fin, etc.)
   * @returns {Promise} Respuesta de la API
   */
  async getVentasAutomotrices(params = {}) {
    return this.makeApiCall('/ventas/automotrices', 'GET', null, params);
  }

  /**
   * Crear una nueva venta de producto automotriz
   * @param {object} data - Datos de la venta automotriz
   * @returns {Promise} Respuesta de la API
   */
  async createVentaAutomotriz(data) {
    return this.makeApiCall('/ventas/automotrices', 'POST', data);
  }

  /**
   * Obtener una venta automotriz específica por ID
   * @param {number} id - ID de la venta
   * @returns {Promise} Respuesta de la API
   */
  async getVentaAutomotriz(id) {
    return this.makeApiCall(`/ventas/automotrices/${id}`, 'GET');
  }

  /**
   * Actualizar una venta automotriz existente
   * @param {number} id - ID de la venta
   * @param {object} data - Datos actualizados de la venta
   * @returns {Promise} Respuesta de la API
   */
  async updateVentaAutomotriz(id, data) {
    return this.makeApiCall(`/ventas/automotrices/${id}`, 'PUT', data);
  }

  /**
   * Eliminar una venta automotriz
   * @param {number} id - ID de la venta
   * @returns {Promise} Respuesta de la API
   */
  async deleteVentaAutomotriz(id) {
    return this.makeApiCall(`/ventas/automotrices/${id}`, 'DELETE');
  }

  /**
   * Obtener métricas específicas de ventas automotrices
   * @param {object} params - Parámetros de consulta (fechas, filtros, etc.)
   * @returns {Promise} Respuesta de la API
   */
  async getMetricasAutomotrices(params = {}) {
    return this.makeApiCall('/ventas/automotrices/metricas', 'GET', null, params);
  }

  // =================================================================
  // MÉTODOS PARA VENTAS DE DESPENSA
  // =================================================================

  /**
   * Obtener todas las ventas de productos de despensa
   * @param {object} params - Parámetros de consulta (fecha_inicio, fecha_fin, etc.)
   * @returns {Promise} Respuesta de la API
   */
  async getVentasDespensa(params = {}) {
    return this.makeApiCall('/ventas/despensa', 'GET', null, params);
  }

  /**
   * Crear una nueva venta de producto de despensa
   * @param {object} data - Datos de la venta de despensa
   * @returns {Promise} Respuesta de la API
   */
  async createVentaDespensa(data) {
    return this.makeApiCall('/ventas/despensa', 'POST', data);
  }

  /**
   * Obtener una venta de despensa específica por ID
   * @param {number} id - ID de la venta
   * @returns {Promise} Respuesta de la API
   */
  async getVentaDespensa(id) {
    return this.makeApiCall(`/ventas/despensa/${id}`, 'GET');
  }

  /**
   * Actualizar una venta de despensa existente
   * @param {number} id - ID de la venta
   * @param {object} data - Datos actualizados de la venta
   * @returns {Promise} Respuesta de la API
   */
  async updateVentaDespensa(id, data) {
    return this.makeApiCall(`/ventas/despensa/${id}`, 'PUT', data);
  }

  /**
   * Eliminar una venta de despensa
   * @param {number} id - ID de la venta
   * @returns {Promise} Respuesta de la API
   */
  async deleteVentaDespensa(id) {
    return this.makeApiCall(`/ventas/despensa/${id}`, 'DELETE');
  }

  /**
   * Obtener métricas específicas de ventas de despensa
   * @param {object} params - Parámetros de consulta (fechas, filtros, etc.)
   * @returns {Promise} Respuesta de la API
   */
  async getMetricasDespensa(params = {}) {
    return this.makeApiCall('/ventas/despensa/metricas', 'GET', null, params);
  }

  // =================================================================
  // MÉTODOS UTILITARIOS Y DE CONVENIENCIA
  // =================================================================

  /**
   * Obtener ventas por rango de fechas (todos los tipos)
   * @param {string} fechaInicio - Fecha de inicio (YYYY-MM-DD)
   * @param {string} fechaFin - Fecha de fin (YYYY-MM-DD)
   * @returns {Promise} Respuesta de la API
   */
  async getVentasByFechaRange(fechaInicio, fechaFin) {
    return this.getAll({ fecha_inicio: fechaInicio, fecha_fin: fechaFin });
  }

  /**
   * Obtener ventas automotrices por rango de fechas
   * @param {string} fechaInicio - Fecha de inicio (YYYY-MM-DD)
   * @param {string} fechaFin - Fecha de fin (YYYY-MM-DD)
   * @returns {Promise} Respuesta de la API
   */
  async getVentasAutomotricesByFechaRange(fechaInicio, fechaFin) {
    return this.getVentasAutomotrices({ fecha_inicio: fechaInicio, fecha_fin: fechaFin });
  }

  /**
   * Obtener ventas de despensa por rango de fechas
   * @param {string} fechaInicio - Fecha de inicio (YYYY-MM-DD)
   * @param {string} fechaFin - Fecha de fin (YYYY-MM-DD)
   * @returns {Promise} Respuesta de la API
   */
  async getVentasDespensaByFechaRange(fechaInicio, fechaFin) {
    return this.getVentasDespensa({ fecha_inicio: fechaInicio, fecha_fin: fechaFin });
  }

  /**
   * Obtener ventas por cliente
   * @param {number} clienteId - ID del cliente
   * @returns {Promise} Respuesta de la API
   */
  async getVentasByCliente(clienteId) {
    return this.getAll({ cliente_id: clienteId });
  }

  /**
   * Verificar disponibilidad de stock antes de venta
   * @param {string} tipoProducto - 'automotriz' o 'despensa'
   * @param {number} productoId - ID del producto
   * @param {number} cantidad - Cantidad requerida
   * @returns {Promise<boolean>} True si hay stock suficiente
   */
  async verificarStock(tipoProducto, productoId, cantidad) {
    try {
      const productos = await this.getProductosDisponibles();
      const tipoKey = tipoProducto === 'automotriz' ? 'automotrices' : 'despensa';
      
      if (productos[tipoKey]) {
        const producto = productos[tipoKey].find(p => p.id === productoId);
        return producto && producto.stock >= cantidad;
      }
      
      return false;
    } catch (error) {
      console.error('Error al verificar stock:', error);
      return false;
    }
  }

  // =================================================================
  // MÉTODOS PRIVADOS PARA APLICAR DRY
  // =================================================================

  /**
   * Método privado para hacer llamadas a la API (DRY)
   * @param {string} endpoint - Endpoint de la API
   * @param {string} method - Método HTTP (GET, POST, PUT, DELETE)
   * @param {object|null} data - Datos para POST/PUT
   * @param {object} params - Parámetros de consulta para GET
   * @returns {Promise} Respuesta de la API
   */
  async makeApiCall(endpoint, method = 'GET', data = null, params = {}) {
    try {
      let response;
      const config = { params };

      switch (method.toUpperCase()) {
        case 'GET':
          response = await api.get(endpoint, config);
          break;
        case 'POST':
          response = await api.post(endpoint, data);
          break;
        case 'PUT':
          response = await api.put(endpoint, data);
          break;
        case 'DELETE':
          response = await api.delete(endpoint);
          break;
        default:
          throw new Error(`Método HTTP no soportado: ${method}`);
      }

      return response.data;
    } catch (error) {
      console.error(`Error en ${method} ${endpoint}:`, error);
      throw this.processApiError(error);
    }
  }

  /**
   * Procesar errores de la API de forma centralizada
   * @param {Error} error - Error de la API
   * @returns {Error} Error procesado
   */
  processApiError(error) {
    if (error.response && error.response.data && error.response.data.message) {
      return new Error(error.response.data.message);
    }
    
    if (error.response && error.response.status === 422) {
      return new Error('Datos de venta inválidos. Verifique la información ingresada.');
    }
    
    if (error.response && error.response.status === 404) {
      return new Error('Venta no encontrada.');
    }
    
    if (error.response && error.response.status === 500) {
      return new Error('Error interno del servidor. Contacte al administrador.');
    }
    
    return new Error('Error desconocido en la operación de venta.');
  }

  /**
   * Formatear respuesta de venta para la UI
   * @param {object} ventaResponse - Respuesta de la API
   * @returns {object} Datos formateados para la UI
   */
  formatVentaForUI(ventaResponse) {
    if (!ventaResponse || !ventaResponse.data) {
      return null;
    }

    return {
      ...ventaResponse.data,
      fecha_formatted: new Date(ventaResponse.data.fecha_venta).toLocaleDateString('es-ES'),
      monto_formatted: new Intl.NumberFormat('es-ES', {
        style: 'currency',
        currency: 'EUR'
      }).format(ventaResponse.data.monto_total)
    };
  }

  /**
   * Procesar errores de la API de ventas (método legacy para compatibilidad)
   * @deprecated Usar processApiError en su lugar
   * @param {Error} error - Error de la API
   * @returns {string} Mensaje de error formateado
   */
  handleVentaError(error) {
    console.warn('handleVentaError está deprecated. Use processApiError en su lugar.');
    return this.processApiError(error).message;
  }
}

// Crear instancia única del servicio
export const ventaService = new VentaServiceClass();
export default ventaService;
