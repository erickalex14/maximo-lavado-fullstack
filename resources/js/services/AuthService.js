import apiClient from './api';

export class AuthService {
  /**
   * Iniciar sesión
   */
  static async login(credentials) {
    const response = await apiClient.post('/login', credentials);
    return response.data;
  }

  /**
   * Cerrar sesión
   */
  static async logout() {
    const response = await apiClient.post('/logout');
    return response.data;
  }

  /**
   * Obtener usuario autenticado
   */
  static async getUser() {
    const response = await apiClient.get('/user');
    return response.data;
  }

  /**
   * Verificar si el token es válido
   */
  static async validateToken() {
    try {
      const response = await apiClient.get('/user');
      return { valid: true, user: response.data };
    } catch (error) {
      return { valid: false, user: null };
    }
  }
}

export default AuthService;
