import axios, { type AxiosInstance, type AxiosResponse, type InternalAxiosRequestConfig } from 'axios';
import type { ApiResponse } from '@/types';

class ApiService {
  private api: AxiosInstance;

  constructor() {
    this.api = axios.create({
      baseURL: '/api',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      withCredentials: true,
    });

    // Configurar XSRF token header para Laravel Sanctum
    this.api.defaults.xsrfCookieName = 'XSRF-TOKEN';
    this.api.defaults.xsrfHeaderName = 'X-XSRF-TOKEN';

    this.setupInterceptors();
  }

  private setupInterceptors(): void {
    // Request interceptor para agregar token
    this.api.interceptors.request.use(
      (config: InternalAxiosRequestConfig) => {
        const token = this.getToken();
        if (token && config.headers) {
          config.headers.Authorization = `Bearer ${token}`;
        }
        return config;
      },
      (error) => {
        return Promise.reject(error);
      }
    );

    // Response interceptor para manejar errores globales
    this.api.interceptors.response.use(
      (response: AxiosResponse) => {
        return response;
      },
      (error) => {
        // Solo redirigir automáticamente en ciertas condiciones
        if (error.response?.status === 401) {
          // Solo redirigir si no estamos ya en la página de login
          if (!window.location.pathname.includes('/login')) {
            this.removeToken();
            // Usar router para navegar en lugar de window.location
            if (window.location.pathname !== '/login') {
              console.warn('Token expirado, redirigiendo al login');
              window.location.href = '/login';
            }
          }
        }
        return Promise.reject(error);
      }
    );
  }

  private getToken(): string | null {
    return localStorage.getItem('auth_token');
  }

  private removeToken(): void {
    localStorage.removeItem('auth_token');
  }

  public setToken(token: string): void {
    localStorage.setItem('auth_token', token);
  }

  // Métodos HTTP genericos
  public async get<T = any>(url: string, params?: any): Promise<ApiResponse<T>> {
    const response = await this.api.get(url, { params });
    return response.data;
  }

  public async post<T = any>(url: string, data?: any): Promise<ApiResponse<T>> {
    const response = await this.api.post(url, data);
    return response.data;
  }

  public async put<T = any>(url: string, data?: any): Promise<ApiResponse<T>> {
    const response = await this.api.put(url, data);
    return response.data;
  }

  public async patch<T = any>(url: string, data?: any): Promise<ApiResponse<T>> {
    const response = await this.api.patch(url, data);
    return response.data;
  }

  public async delete<T = any>(url: string): Promise<ApiResponse<T>> {
    const response = await this.api.delete(url);
    return response.data;
  }

  // Método para descargar archivos
  public async download(url: string, filename?: string): Promise<void> {
    const response = await this.api.get(url, {
      responseType: 'blob',
    });

    const blob = new Blob([response.data]);
    const downloadUrl = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = downloadUrl;
    link.download = filename || 'download';
    link.click();
    window.URL.revokeObjectURL(downloadUrl);
  }

  // Método específico para obtener CSRF cookie (Laravel Sanctum)
  public async getCsrfCookie(): Promise<void> {
    await axios.get('/sanctum/csrf-cookie', {
      withCredentials: true,
    });
  }

  // Getter para acceso directo a la instancia de axios si es necesario
  public get instance(): AxiosInstance {
    return this.api;
  }
}

// Instancia singleton
const apiService = new ApiService();

export default apiService;
