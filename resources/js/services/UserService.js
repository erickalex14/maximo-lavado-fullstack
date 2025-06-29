import api from './api';

/**
 * Servicio para gestión de usuarios
 * Consume la API del backend UserController
 * 
 * Aplica principios SOLID:
 * - Single Responsibility: Solo maneja operaciones de usuarios
 * - Open/Closed: Extensible para nuevos tipos de gestión de usuarios
 * - Liskov Substitution: Métodos consistentes
 * - Interface Segregation: Métodos específicos por funcionalidad
 * - Dependency Inversion: Depende de la abstracción API
 * 
 * Endpoints disponibles:
 * - GET /api/usuarios - Listar usuarios
 * - POST /api/usuarios - Crear usuario
 * - GET /api/usuarios/activos - Usuarios activos
 * - GET /api/usuarios/estadisticas - Estadísticas de usuarios
 * - GET /api/usuarios/{id} - Obtener usuario específico
 * - PUT /api/usuarios/{id} - Actualizar usuario
 * - DELETE /api/usuarios/{id} - Eliminar usuario
 * - PUT /api/usuarios/{id}/password - Cambiar contraseña
 * - PUT /api/usuarios/{id}/reset-password - Resetear contraseña
 * - PUT /api/usuarios/{id}/verify-email - Verificar email
 */
class UserServiceClass {
  
  // =================================================================
  // MÉTODOS PRINCIPALES CRUD
  // =================================================================

  /**
   * Obtener todos los usuarios
   * @param {object} params - Parámetros de consulta (filtros, paginación, etc.)
   * @returns {Promise} Respuesta de la API
   */
  async getAll(params = {}) {
    return this.makeApiCall('/usuarios', 'GET', null, params);
  }

  /**
   * Crear un nuevo usuario
   * @param {object} data - Datos del usuario
   * @returns {Promise} Respuesta de la API
   */
  async create(data) {
    return this.makeApiCall('/usuarios', 'POST', data);
  }

  /**
   * Obtener un usuario específico por ID
   * @param {number} id - ID del usuario
   * @returns {Promise} Respuesta de la API
   */
  async getById(id) {
    return this.makeApiCall(`/usuarios/${id}`, 'GET');
  }

  /**
   * Actualizar un usuario existente
   * @param {number} id - ID del usuario
   * @param {object} data - Datos actualizados del usuario
   * @returns {Promise} Respuesta de la API
   */
  async update(id, data) {
    return this.makeApiCall(`/usuarios/${id}`, 'PUT', data);
  }

  /**
   * Eliminar un usuario
   * @param {number} id - ID del usuario
   * @returns {Promise} Respuesta de la API
   */
  async delete(id) {
    return this.makeApiCall(`/usuarios/${id}`, 'DELETE');
  }

  // =================================================================
  // MÉTODOS ESPECÍFICOS DE USUARIOS
  // =================================================================

  /**
   * Obtener usuarios activos
   * @param {object} params - Parámetros opcionales
   * @returns {Promise} Respuesta de la API
   */
  async getActiveUsers(params = {}) {
    return this.makeApiCall('/usuarios/activos', 'GET', null, params);
  }

  /**
   * Obtener estadísticas de usuarios
   * @param {object} params - Parámetros opcionales
   * @returns {Promise} Respuesta de la API
   */
  async getStats(params = {}) {
    return this.makeApiCall('/usuarios/estadisticas', 'GET', null, params);
  }

  /**
   * Cambiar contraseña de un usuario
   * @param {number} id - ID del usuario
   * @param {object} passwordData - Datos de contraseña (current_password, new_password, new_password_confirmation)
   * @returns {Promise} Respuesta de la API
   */
  async updatePassword(id, passwordData) {
    return this.makeApiCall(`/usuarios/${id}/password`, 'PUT', passwordData);
  }

  /**
   * Resetear contraseña de un usuario (admin)
   * @param {number} id - ID del usuario
   * @param {object} data - Datos para el reset (nueva contraseña temporal)
   * @returns {Promise} Respuesta de la API
   */
  async resetPassword(id, data = {}) {
    return this.makeApiCall(`/usuarios/${id}/reset-password`, 'PUT', data);
  }

  /**
   * Verificar email de un usuario
   * @param {number} id - ID del usuario
   * @returns {Promise} Respuesta de la API
   */
  async verifyEmail(id) {
    return this.makeApiCall(`/usuarios/${id}/verify-email`, 'PUT');
  }

  // =================================================================
  // MÉTODOS UTILITARIOS
  // =================================================================

  /**
   * Buscar usuarios por nombre o email
   * @param {string} query - Término de búsqueda
   * @returns {Promise} Respuesta de la API
   */
  async search(query) {
    return this.getAll({ search: query });
  }

  /**
   * Obtener usuarios por rol
   * @param {string} role - Rol del usuario (admin, user, etc.)
   * @returns {Promise} Respuesta de la API
   */
  async getByRole(role) {
    return this.getAll({ role });
  }

  /**
   * Validar disponibilidad de email
   * @param {string} email - Email a validar
   * @param {number} excludeId - ID del usuario a excluir (para edición)
   * @returns {Promise<boolean>} True si el email está disponible
   */
  async isEmailAvailable(email, excludeId = null) {
    try {
      const params = { email };
      if (excludeId) {
        params.exclude_id = excludeId;
      }
      
      const response = await this.getAll(params);
      return response.data && response.data.length === 0;
    } catch (error) {
      console.error('Error al validar email:', error);
      return false;
    }
  }

  /**
   * Formatear usuario para mostrar en la UI
   * @param {object} user - Datos del usuario
   * @returns {object} Usuario formateado
   */
  formatUserForUI(user) {
    if (!user) return null;

    return {
      ...user,
      created_formatted: user.created_at ? 
        new Date(user.created_at).toLocaleDateString('es-ES') : '',
      
      updated_formatted: user.updated_at ? 
        new Date(user.updated_at).toLocaleDateString('es-ES') : '',
      
      status_text: user.email_verified_at ? 'Verificado' : 'Pendiente de verificación',
      
      role_text: this.getRoleText(user.role),
      
      full_info: `${user.name} (${user.email})`
    };
  }

  /**
   * Obtener texto descriptivo del rol
   * @param {string} role - Rol del usuario
   * @returns {string} Texto descriptivo
   */
  getRoleText(role) {
    const roles = {
      'admin': 'Administrador',
      'user': 'Usuario',
      'empleado': 'Empleado',
      'supervisor': 'Supervisor'
    };
    
    return roles[role] || 'Usuario';
  }

  /**
   * Validar datos de usuario antes de enviar
   * @param {object} userData - Datos del usuario
   * @returns {object} Datos validados y formateados
   */
  validateUserData(userData) {
    const requiredFields = ['name', 'email'];
    
    for (const field of requiredFields) {
      if (!userData[field] || userData[field].trim() === '') {
        throw new Error(`El campo ${field} es requerido`);
      }
    }

    // Validar formato de email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(userData.email)) {
      throw new Error('El formato del email no es válido');
    }

    // Validar contraseña si se está creando usuario
    if (userData.password && userData.password.length < 8) {
      throw new Error('La contraseña debe tener al menos 8 caracteres');
    }

    return {
      ...userData,
      name: userData.name.trim(),
      email: userData.email.trim().toLowerCase()
    };
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
      // Errores de validación
      const validationErrors = error.response.data.errors;
      if (validationErrors) {
        const firstError = Object.values(validationErrors)[0];
        return new Error(firstError[0] || 'Datos de usuario inválidos');
      }
      return new Error('Datos de usuario inválidos. Verifique la información ingresada.');
    }
    
    if (error.response && error.response.status === 404) {
      return new Error('Usuario no encontrado.');
    }
    
    if (error.response && error.response.status === 403) {
      return new Error('No tiene permisos para realizar esta acción.');
    }
    
    if (error.response && error.response.status === 500) {
      return new Error('Error interno del servidor. Contacte al administrador.');
    }
    
    return new Error('Error desconocido en la gestión de usuarios.');
  }
}

// Crear instancia única del servicio
export const userService = new UserServiceClass();
export default userService;
