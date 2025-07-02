import apiClient from './api';

/**
 * Servicio base para operaciones CRUD estándar
 */
export class BaseService {
  constructor(endpoint) {
    this.endpoint = endpoint;
    this.enableLogging = false;
  }

  // CRUD Básico

  // Obtiene una lista de recursos

  async index(params = {}) {
    try {
      const response = await apiClient.get(this.endpoint, { params });
      return this._handleResponse(response);
    } catch (error) {
      throw this._handleError(error);
    }
  }

  // Obtiene todos los recursos

  async all(params = {}) {
    try {
      const response = await apiClient.get(`${this.endpoint}/all`, { params });
      return this._handleResponse(response);
    } catch (error) {
      throw this._handleError(error);
    }
  }

  // Obtiene un recurso específico

  async show(id) {
    try {
      const response = await apiClient.get(`${this.endpoint}/${id}`);
      return this._handleResponse(response);
    } catch (error) {
      throw this._handleError(error);
    }
  }

  // Crea un nuevo recurso

  async store(data) {
    try {
      const response = await apiClient.post(this.endpoint, data);
      return this._handleResponse(response);
    } catch (error) {
      throw this._handleError(error);
    }
  }

  // Actualiza un recurso existente

  async update(id, data) {
    try {
      const response = await apiClient.put(`${this.endpoint}/${id}`, data);
      return this._handleResponse(response);
    } catch (error) {
      throw this._handleError(error);
    }
  }

  // Elimina un recurso (soft delete)

  async destroy(id) {
    try {
      const response = await apiClient.delete(`${this.endpoint}/${id}`);
      return this._handleResponse(response);
    } catch (error) {
      throw this._handleError(error);
    }
  }

  // Obtiene recursos eliminados (soft delete)

  async trashed(params = {}) {
    try {
      const response = await apiClient.get(`${this.endpoint}/trashed`, { params });
      return this._handleResponse(response);
    } catch (error) {
      throw this._handleError(error);
    }
  }

  // Restaura un recurso eliminado (soft delete)

  async restore(id) {
    try {
      const response = await apiClient.put(`${this.endpoint}/${id}/restore`);
      return this._handleResponse(response);
    } catch (error) {
      throw this._handleError(error);
    }
  }

  // Búsqueda

  async search(query, params = {}) {
    try {
      const response = await apiClient.get(`${this.endpoint}/search`, {
        params: { q: query, ...params }
      });
      return this._handleResponse(response);
    } catch (error) {
      throw this._handleError(error);
    }
  }

  // Estadísticas

  async stats(params = {}) {
    try {
      const response = await apiClient.get(`${this.endpoint}/stats`, { params });
      return this._handleResponse(response);
    } catch (error) {
      throw this._handleError(error);
    }
  }

  // Métricas

  async metricas(params = {}) {
    try {
      const response = await apiClient.get(`${this.endpoint}/metricas`, { params });
      return this._handleResponse(response);
    } catch (error) {
      throw this._handleError(error);
    }
  }

  // Filtros temporales

  async getByDay(fecha, params = {}) {
    try {
      const response = await apiClient.get(`${this.endpoint}/dia`, {
        params: { fecha, ...params }
      });
      return this._handleResponse(response);
    } catch (error) {
      throw this._handleError(error);
    }
  }

  async getByWeek(fecha, params = {}) {
    try {
      const response = await apiClient.get(`${this.endpoint}/semana`, {
        params: { fecha, ...params }
      });
      return this._handleResponse(response);
    } catch (error) {
      throw this._handleError(error);
    }
  }

  async getByMonth(anio, mes, params = {}) {
    try {
      const response = await apiClient.get(`${this.endpoint}/mes`, {
        params: { anio, mes, ...params }
      });
      return this._handleResponse(response);
    } catch (error) {
      throw this._handleError(error);
    }
  }

  async getByYear(anio, params = {}) {
    try {
      const response = await apiClient.get(`${this.endpoint}/anio`, {
        params: { anio, ...params }
      });
      return this._handleResponse(response);
    } catch (error) {
      throw this._handleError(error);
    }
  }

  // Acciones específicas

  // Activa o desactiva un recurso
  async toggleActivo(id) {
    try {
      const response = await apiClient.patch(`${this.endpoint}/${id}/toggle-activo`);
      return this._handleResponse(response);
    } catch (error) {
      throw this._handleError(error);
    }
  }

  // Cambia el estado de un recurso
  async changeStatus(id, estado) {
    try {
      const response = await apiClient.put(`${this.endpoint}/${id}/estado`, { estado });
      return this._handleResponse(response);
    } catch (error) {
      throw this._handleError(error);
    }
  }

  // Actualiza el stock de un recurso
  
  async updateStock(id, cantidad) {
    try {
      const response = await apiClient.put(`${this.endpoint}/${id}/stock`, { cantidad });
      return this._handleResponse(response);
    } catch (error) {
      throw this._handleError(error);
    }
  }

  // Acción personalizada
  async customAction(action, options = {}) {
    const { id = null, data = null, method = 'POST', useParams = false } = options;
    
    try {
      const url = id ? `${this.endpoint}/${id}/${action}` : `${this.endpoint}/${action}`;
      
      const config = { method: method.toLowerCase(), url };

      if (data) {
        if (useParams || ['get', 'delete'].includes(method.toLowerCase())) {
          config.params = data;
        } else {
          config.data = data;
        }
      }

      const response = await apiClient(config);
      return this._handleResponse(response);
    } catch (error) {
      throw this._handleError(error);
    }
  }

  // Métodos internos
  _handleResponse(response) {
    if (this.enableLogging) {
      console.log(`✅ ${this.endpoint}:`, response.data);
    }

    // El backend retorna: { success: true, data: {...}, message: "..." }
    if (response.data && typeof response.data === 'object' && 'data' in response.data) {
      return response.data.data;
    }
    
    return response.data;
  }

  _handleError(error) {
    if (this.enableLogging) {
      console.error(`❌ ${this.endpoint}:`, error);
    }

    if (error.response) {
      const { status, data } = error.response;
      const message = data?.message || `Error ${status}`;
      
      const customError = new Error(message);
      customError.status = status;
      customError.data = data;
      customError.validation = data?.errors;
      
      return customError;
    } else if (error.request) {
      const networkError = new Error('Error de conexión con el servidor');
      networkError.status = 0;
      return networkError;
    } else {
      return error;
    }
  }

  // Configuración
  setLogging(enable) {
    this.enableLogging = enable;
    return this;
  }

  getEndpoint() {
    return this.endpoint;
  }
}

// Factory
export const createService = (endpoint, options = {}) => {
  const service = new BaseService(endpoint);
  
  if (options.enableLogging) {
    service.setLogging(true);
  }
  
  return service;
};

export default BaseService;
