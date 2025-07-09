import apiService from './api';
import type { LoginCredentials, AuthUser, ApiResponse } from '@/types';

class AuthService {
  /**
   * Obtener CSRF cookie antes de realizar peticiones autenticadas
   * GET /sanctum/csrf-cookie
   */
  async getCsrfCookie(): Promise<void> {
    try {
      await apiService.getCsrfCookie();
    } catch (error) {
      console.warn('Error al obtener CSRF cookie:', error);
    }
  }

  /**
   * Iniciar sesión
   * POST /api/login
   */
  async login(credentials: LoginCredentials): Promise<ApiResponse<{ user: AuthUser; token: string }>> {
    // Obtener CSRF cookie antes del login
    await this.getCsrfCookie();
    
    const response = await apiService.post('/login', credentials);
    
    if (response.success && response.data?.token) {
      apiService.setToken(response.data.token);
    }
    
    return response;
  }

  /**
   * Cerrar sesión
   * POST /api/logout
   */
  async logout(): Promise<ApiResponse> {
    const response = await apiService.post('/logout');
    
    // Remover token independientemente de la respuesta
    localStorage.removeItem('auth_token');
    
    return response;
  }

  /**
   * Obtener usuario autenticado
   * GET /api/usuario
   */
  async getUser(): Promise<ApiResponse<AuthUser>> {
    try {
      return await apiService.get('/usuario');
    } catch (error: any) {
      // Si es un error 401, limpiar el token
      if (error.response?.status === 401) {
        this.removeToken();
      }
      throw error;
    }
  }

  /**
   * Verificar si hay un token almacenado
   */
  hasToken(): boolean {
    return !!localStorage.getItem('auth_token');
  }

  /**
   * Obtener token almacenado
   */
  getToken(): string | null {
    return localStorage.getItem('auth_token');
  }

  /**
   * Remover token
   */
  removeToken(): void {
    localStorage.removeItem('auth_token');
  }
}

export default new AuthService();
