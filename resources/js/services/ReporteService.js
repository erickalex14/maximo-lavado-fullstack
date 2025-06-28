import api from './api';

/**
 * Servicio para gestión de reportes
 * Consume la API real del backend sin datos de ejemplo
 * Endpoints disponibles según routes/api.php:
 * - GET /reportes - Lista de reportes disponibles
 * - GET /reportes/ventas - Reporte de ventas
 * - GET /reportes/lavados - Reporte de lavados
 * - GET /reportes/ingresos - Reporte de ingresos
 * - GET /reportes/egresos - Reporte de egresos
 * - GET /reportes/facturas - Reporte de facturas
 * - GET /reportes/empleados - Reporte de empleados
 * - GET /reportes/productos - Reporte de productos
 * - GET /reportes/clientes - Reporte de clientes
 * - GET /reportes/financiero - Reporte financiero
 * - GET /reportes/balance - Reporte de balance
 * - GET /reportes/completo - Reporte completo
 */
class ReporteServiceClass {
  /**
   * Obtener lista de reportes disponibles
   * @returns {Promise} Respuesta de la API
   */
  async getReportesDisponibles() {
    const response = await api.get('/reportes');
    return response.data;
  }

  /**
   * Generar reporte de ventas
   * @param {string} fechaInicio - Fecha inicio (YYYY-MM-DD)
   * @param {string} fechaFin - Fecha fin (YYYY-MM-DD)
   * @param {string} formato - Formato del reporte (json, pdf, excel)
   * @returns {Promise} Respuesta de la API
   */
  async reporteVentas(fechaInicio, fechaFin, formato = 'json') {
    const response = await api.get('/reportes/ventas', {
      params: {
        fecha_inicio: fechaInicio,
        fecha_fin: fechaFin,
        formato
      }
    });
    return response.data;
  }

  /**
   * Generar reporte de lavados
   * @param {string} fechaInicio - Fecha inicio (YYYY-MM-DD)
   * @param {string} fechaFin - Fecha fin (YYYY-MM-DD)
   * @param {string} formato - Formato del reporte (json, pdf, excel)
   * @returns {Promise} Respuesta de la API
   */
  async reporteLavados(fechaInicio, fechaFin, formato = 'json') {
    const response = await api.get('/reportes/lavados', {
      params: {
        fecha_inicio: fechaInicio,
        fecha_fin: fechaFin,
        formato
      }
    });
    return response.data;
  }

  /**
   * Generar reporte de ingresos
   * @param {string} fechaInicio - Fecha inicio (YYYY-MM-DD)
   * @param {string} fechaFin - Fecha fin (YYYY-MM-DD)
   * @param {string} formato - Formato del reporte (json, pdf, excel)
   * @returns {Promise} Respuesta de la API
   */
  async reporteIngresos(fechaInicio, fechaFin, formato = 'json') {
    const response = await api.get('/reportes/ingresos', {
      params: {
        fecha_inicio: fechaInicio,
        fecha_fin: fechaFin,
        formato
      }
    });
    return response.data;
  }

  /**
   * Generar reporte de egresos
   * @param {string} fechaInicio - Fecha inicio (YYYY-MM-DD)
   * @param {string} fechaFin - Fecha fin (YYYY-MM-DD)
   * @param {string} formato - Formato del reporte (json, pdf, excel)
   * @returns {Promise} Respuesta de la API
   */
  async reporteEgresos(fechaInicio, fechaFin, formato = 'json') {
    const response = await api.get('/reportes/egresos', {
      params: {
        fecha_inicio: fechaInicio,
        fecha_fin: fechaFin,
        formato
      }
    });
    return response.data;
  }

  /**
   * Generar reporte de facturas
   * @param {string} fechaInicio - Fecha inicio (YYYY-MM-DD)
   * @param {string} fechaFin - Fecha fin (YYYY-MM-DD)
   * @param {string} formato - Formato del reporte (json, pdf, excel)
   * @returns {Promise} Respuesta de la API
   */
  async reporteFacturas(fechaInicio, fechaFin, formato = 'json') {
    const response = await api.get('/reportes/facturas', {
      params: {
        fecha_inicio: fechaInicio,
        fecha_fin: fechaFin,
        formato
      }
    });
    return response.data;
  }

  /**
   * Generar reporte de empleados
   * @param {string} fechaInicio - Fecha inicio (YYYY-MM-DD)
   * @param {string} fechaFin - Fecha fin (YYYY-MM-DD)
   * @param {string} formato - Formato del reporte (json, pdf, excel)
   * @returns {Promise} Respuesta de la API
   */
  async reporteEmpleados(fechaInicio, fechaFin, formato = 'json') {
    const response = await api.get('/reportes/empleados', {
      params: {
        fecha_inicio: fechaInicio,
        fecha_fin: fechaFin,
        formato
      }
    });
    return response.data;
  }

  /**
   * Generar reporte de productos
   * @param {string} formato - Formato del reporte (json, pdf, excel)
   * @returns {Promise} Respuesta de la API
   */
  async reporteProductos(formato = 'json') {
    const response = await api.get('/reportes/productos', {
      params: { formato }
    });
    return response.data;
  }

  /**
   * Generar reporte de clientes
   * @param {string} formato - Formato del reporte (json, pdf, excel)
   * @returns {Promise} Respuesta de la API
   */
  async reporteClientes(formato = 'json') {
    const response = await api.get('/reportes/clientes', {
      params: { formato }
    });
    return response.data;
  }

  /**
   * Generar reporte financiero
   * @param {string} fechaInicio - Fecha inicio (YYYY-MM-DD)
   * @param {string} fechaFin - Fecha fin (YYYY-MM-DD)
   * @param {string} formato - Formato del reporte (json, pdf, excel)
   * @returns {Promise} Respuesta de la API
   */
  async reporteFinanciero(fechaInicio, fechaFin, formato = 'json') {
    const response = await api.get('/reportes/financiero', {
      params: {
        fecha_inicio: fechaInicio,
        fecha_fin: fechaFin,
        formato
      }
    });
    return response.data;
  }

  /**
   * Generar reporte de balance
   * @param {string} fechaInicio - Fecha inicio (YYYY-MM-DD)
   * @param {string} fechaFin - Fecha fin (YYYY-MM-DD)
   * @param {string} formato - Formato del reporte (json, pdf, excel)
   * @returns {Promise} Respuesta de la API
   */
  async reporteBalance(fechaInicio, fechaFin, formato = 'json') {
    const response = await api.get('/reportes/balance', {
      params: {
        fecha_inicio: fechaInicio,
        fecha_fin: fechaFin,
        formato
      }
    });
    return response.data;
  }

  /**
   * Generar reporte completo del negocio
   * @param {string} fechaInicio - Fecha inicio (YYYY-MM-DD)
   * @param {string} fechaFin - Fecha fin (YYYY-MM-DD)
   * @returns {Promise} Respuesta de la API
   */
  async reporteCompleto(fechaInicio, fechaFin) {
    const response = await api.get('/reportes/completo', {
      params: {
        fecha_inicio: fechaInicio,
        fecha_fin: fechaFin
      }
    });
    return response.data;
  }
}

// Crear instancia única del servicio
export const reporteService = new ReporteServiceClass();
export default reporteService;
