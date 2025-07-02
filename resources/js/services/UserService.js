import { BaseService } from './BaseService';

/**
 * Servicio para gestionar usuarios
 */
class UserService extends BaseService {
  constructor() {
    super('/usuarios');
  }

  // Métodos específicos de usuarios

  // Obtener usuarios activos
  async getActiveUsers(params = {}) {
    try {
      const response = await this.customAction('activos', {
        method: 'GET',
        data: params,
        useParams: true
      });
      return response;
    } catch (error) {
      throw error;
    }
  }

  // Obtener estadísticas de usuarios
  async getStats(params = {}) {
    try {
      const response = await this.customAction('estadisticas', {
        method: 'GET',
        data: params,
        useParams: true
      });
      return response;
    } catch (error) {
      throw error;
    }
  }

  // Actualizar contraseña
  async updatePassword(id, passwordData) {
    try {
      const response = await this.customAction('password', {
        id,
        method: 'PUT',
        data: passwordData
      });
      return response;
    } catch (error) {
      throw error;
    }
  }

  // Resetear contraseña
  async resetPassword(id, resetData = {}) {
    try {
      const response = await this.customAction('reset-password', {
        id,
        method: 'PUT',
        data: resetData
      });
      return response;
    } catch (error) {
      throw error;
    }
  }

  // Verificar email
  async verifyEmail(id, verificationData = {}) {
    try {
      const response = await this.customAction('verify-email', {
        id,
        method: 'PUT',
        data: verificationData
      });
      return response;
    } catch (error) {
      throw error;
    }
  }
}

// Instancia única del servicio
const userService = new UserService();

export default userService;
