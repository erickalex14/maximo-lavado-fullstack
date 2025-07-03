import apiService from './api';
import type { LoginCredentials, AuthUser, ApiResponse } from '@/types';

class AuthService {
  /**
   * Iniciar sesión
   * POST /api/login
   */
  async login(credentials: LoginCredentials): Promise<ApiResponse<{ user: AuthUser; token: string }>> {
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
   * GET /api/user
   */
  async getUser(): Promise<ApiResponse<AuthUser>> {
    return await apiService.get('/user');
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
