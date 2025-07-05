import { defineStore } from 'pinia';
import { ref, computed, readonly } from 'vue';
import authService from '@/services/auth.service';
import type { AuthUser, LoginCredentials } from '@/types';

export const useAuthStore = defineStore('auth', () => {
  // Estado
  const user = ref<AuthUser | null>(null);
  const loading = ref(false);
  const error = ref<string | null>(null);

  // Getters
  const isAuthenticated = computed(() => !!user.value);
  const userName = computed(() => user.value?.name || '');
  const userEmail = computed(() => user.value?.email || '');

  // Actions
  async function login(credentials: LoginCredentials): Promise<boolean> {
    loading.value = true;
    error.value = null;

    try {
      const response = await authService.login(credentials);
      
      if (response.success && response.data) {
        user.value = response.data.user;
        return true;
      } else {
        error.value = response.message || 'Error al iniciar sesión';
        return false;
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Error de conexión';
      return false;
    } finally {
      loading.value = false;
    }
  }

  async function logout(): Promise<void> {
    loading.value = true;

    try {
      await authService.logout();
    } catch (err) {
      console.error('Error al cerrar sesión:', err);
    } finally {
      user.value = null;
      authService.removeToken();
      localStorage.removeItem('user_last_fetch');
      loading.value = false;
    }
  }

  async function fetchUser(): Promise<boolean> {
    console.log('fetchUser called');
    
    if (!authService.hasToken()) {
      console.log('No token found');
      return false;
    }

    // Si ya tenemos un usuario y no ha pasado mucho tiempo, no hacer la petición
    if (user.value && !shouldRefreshUser()) {
      console.log('Using cached user');
      return true;
    }

    loading.value = true;
    error.value = null;

    try {
      console.log('Attempting to get user from API...');
      const response = await authService.getUser();
      
      console.log('User API response:', response);
      
      if (response.success && response.data) {
        user.value = response.data;
        setLastUserFetch();
        console.log('User fetched successfully:', user.value);
        return true;
      } else {
        console.log('Failed to get user, clearing session');
        authService.removeToken();
        user.value = null;
        return false;
      }
    } catch (err: any) {
      console.error('Error al obtener usuario:', err);
      
      // Si es un error 401, limpiar la sesión
      if (err.response?.status === 401) {
        console.log('401 error, clearing session');
        authService.removeToken();
        user.value = null;
      }
      
      error.value = err.response?.data?.message || 'Error al obtener usuario';
      return false;
    } finally {
      loading.value = false;
    }
  }

  // Función para verificar si debemos refrescar el usuario
  function shouldRefreshUser(): boolean {
    const lastFetch = localStorage.getItem('user_last_fetch');
    if (!lastFetch) return true;
    
    const now = Date.now();
    const lastFetchTime = parseInt(lastFetch);
    const timeDiff = now - lastFetchTime;
    
    // Refrescar si han pasado más de 5 minutos
    return timeDiff > 5 * 60 * 1000;
  }

  // Función para guardar el timestamp de la última consulta
  function setLastUserFetch(): void {
    localStorage.setItem('user_last_fetch', Date.now().toString());
  }

  function clearError(): void {
    error.value = null;
  }

  function $reset(): void {
    user.value = null;
    loading.value = false;
    error.value = null;
    authService.removeToken();
    localStorage.removeItem('user_last_fetch');
  }

  return {
    // Estado
    user: readonly(user),
    loading: readonly(loading),
    error: readonly(error),
    
    // Getters
    isAuthenticated,
    userName,
    userEmail,
    
    // Actions
    login,
    logout,
    fetchUser,
    clearError,
    $reset,
  };
});
