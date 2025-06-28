import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import AuthService from '@/services/AuthService';

export const useAuthStore = defineStore('auth', () => {
  // Estado
  const user = ref(null);
  const token = ref(localStorage.getItem('auth_token'));
  const isLoading = ref(false);
  const isInitialized = ref(false);

  // Getters
  const isAuthenticated = computed(() => !!token.value && !!user.value);
  const userName = computed(() => user.value?.name || '');
  const userEmail = computed(() => user.value?.email || '');

  // Actions
  const login = async (credentials) => {
    try {
      isLoading.value = true;
      const response = await AuthService.login(credentials);
      
      if (response.token && response.user) {
        token.value = response.token;
        user.value = response.user;
        
        // Guardar en localStorage
        localStorage.setItem('auth_token', response.token);
        localStorage.setItem('user', JSON.stringify(response.user));
        
        return { success: true, message: 'Inicio de sesión exitoso' };
      } else {
        throw new Error('Respuesta inválida del servidor');
      }
    } catch (error) {
      const message = error.response?.data?.message || 'Error al iniciar sesión';
      return { success: false, message };
    } finally {
      isLoading.value = false;
    }
  };

  const logout = async () => {
    try {
      isLoading.value = true;
      await AuthService.logout();
    } catch (error) {
      console.error('Error al cerrar sesión:', error);
    } finally {
      // Limpiar estado local
      clearAuthData();
      isLoading.value = false;
    }
  };

  const checkAuth = async () => {
    if (!token.value) {
      isInitialized.value = true;
      return false;
    }

    try {
      isLoading.value = true;
      const validation = await AuthService.validateToken();
      
      if (validation.valid && validation.user) {
        user.value = validation.user;
        localStorage.setItem('user', JSON.stringify(validation.user));
        isInitialized.value = true;
        return true;
      } else {
        clearAuthData();
        isInitialized.value = true;
        return false;
      }
    } catch (error) {
      console.error('Error al verificar autenticación:', error);
      clearAuthData();
      isInitialized.value = true;
      return false;
    } finally {
      isLoading.value = false;
    }
  };

  const clearAuthData = () => {
    user.value = null;
    token.value = null;
    isInitialized.value = true;
    localStorage.removeItem('auth_token');
    localStorage.removeItem('user');
  };

  // Inicializar con datos del localStorage si existen
  const initializeFromStorage = () => {
    const savedUser = localStorage.getItem('user');
    if (savedUser && token.value) {
      try {
        user.value = JSON.parse(savedUser);
        isInitialized.value = true;
      } catch (error) {
        console.error('Error al parsear usuario guardado:', error);
        clearAuthData();
      }
    } else {
      isInitialized.value = true;
    }
  };

  // Inicializar al crear el store
  initializeFromStorage();

  return {
    // Estado
    user,
    token,
    isLoading,
    isInitialized,
    // Getters
    isAuthenticated,
    userName,
    userEmail,
    // Actions
    login,
    logout,
    checkAuth,
    clearAuthData
  };
});
