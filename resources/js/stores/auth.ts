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
      loading.value = false;
    }
  }

  async function fetchUser(): Promise<boolean> {
    if (!authService.hasToken()) {
      return false;
    }

    loading.value = true;
    error.value = null;

    try {
      const response = await authService.getUser();
      
      if (response.success && response.data) {
        user.value = response.data;
        return true;
      } else {
        authService.removeToken();
        return false;
      }
    } catch (err: any) {
      authService.removeToken();
      error.value = err.response?.data?.message || 'Error al obtener usuario';
      return false;
    } finally {
      loading.value = false;
    }
  }

  function clearError(): void {
    error.value = null;
  }

  function $reset(): void {
    user.value = null;
    loading.value = false;
    error.value = null;
    authService.removeToken();
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
