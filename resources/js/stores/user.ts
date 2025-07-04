import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { userService } from '@/services/user.service';
import type { 
  User, 
  UserStats,
  CreateUserForm,
  UpdateUserForm,
  UpdatePasswordForm,
  ResetPasswordForm
} from '@/types';
import type { 
  CreateUserRequest, 
  UpdateUserRequest, 
  UpdatePasswordRequest,
  ResetPasswordRequest
} from '@/services/user.service';

export const useUserStore = defineStore('user', () => {
  // State
  const users = ref<User[]>([]);
  const activeUsers = ref<User[]>([]);
  const trashedUsers = ref<User[]>([]);
  const currentUser = ref<User | null>(null);
  const userStats = ref<UserStats | null>(null);
  const loading = ref(false);
  const error = ref<string | null>(null);

  // Paginación
  const currentPage = ref(1);
  const perPage = ref(10);
  const searchQuery = ref('');

  // Computed
  const totalUsers = computed(() => users.value.length);
  const totalPages = computed(() => Math.ceil(filteredUsers.value.length / perPage.value));
  
  const filteredUsers = computed(() => {
    if (!searchQuery.value) return users.value;
    
    const query = searchQuery.value.toLowerCase();
    return users.value.filter((user: User) => 
      user.name.toLowerCase().includes(query) ||
      user.email.toLowerCase().includes(query)
    );
  });

  const paginatedUsers = computed(() => {
    const start = (currentPage.value - 1) * perPage.value;
    const end = start + perPage.value;
    return filteredUsers.value.slice(start, end);
  });

  const hasUsers = computed(() => users.value.length > 0);
  const hasTrashedUsers = computed(() => trashedUsers.value.length > 0);

  // Actions
  async function fetchUsers() {
    try {
      loading.value = true;
      error.value = null;
      users.value = await userService.getUsers();
    } catch (err: any) {
      error.value = err.message || 'Error al cargar usuarios';
      console.error('Error fetching users:', err);
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function fetchActiveUsers() {
    try {
      loading.value = true;
      error.value = null;
      activeUsers.value = await userService.getActiveUsers();
    } catch (err: any) {
      error.value = err.message || 'Error al cargar usuarios activos';
      console.error('Error fetching active users:', err);
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function fetchTrashedUsers() {
    try {
      loading.value = true;
      error.value = null;
      trashedUsers.value = await userService.getTrashedUsers();
    } catch (err: any) {
      error.value = err.message || 'Error al cargar usuarios eliminados';
      console.error('Error fetching trashed users:', err);
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function fetchUserById(id: number) {
    try {
      loading.value = true;
      error.value = null;
      currentUser.value = await userService.getUserById(id);
      return currentUser.value;
    } catch (err: any) {
      error.value = err.message || 'Error al cargar usuario';
      console.error('Error fetching user:', err);
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function createUser(data: CreateUserRequest) {
    try {
      loading.value = true;
      error.value = null;
      
      const newUser = await userService.createUser(data);
      users.value.unshift(newUser);
      
      return newUser;
    } catch (err: any) {
      error.value = err.response?.data?.message || err.message || 'Error al crear usuario';
      console.error('Error creating user:', err);
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function updateUser(id: number, data: UpdateUserRequest) {
    try {
      loading.value = true;
      error.value = null;
      
      const updatedUser = await userService.updateUser(id, data);
      
      const index = users.value.findIndex((u: User) => u.id === id);
      if (index !== -1) {
        users.value[index] = updatedUser;
      }
      
      if (currentUser.value?.id === id) {
        currentUser.value = updatedUser;
      }
      
      return updatedUser;
    } catch (err: any) {
      error.value = err.response?.data?.message || err.message || 'Error al actualizar usuario';
      console.error('Error updating user:', err);
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function deleteUser(id: number) {
    try {
      loading.value = true;
      error.value = null;
      
      await userService.deleteUser(id);
      
      // Mover de users a trashedUsers
      const userIndex = users.value.findIndex((u: User) => u.id === id);
      if (userIndex !== -1) {
        const deletedUser = users.value[userIndex];
        users.value.splice(userIndex, 1);
        trashedUsers.value.unshift({ ...deletedUser, deleted_at: new Date().toISOString() });
      }
      
    } catch (err: any) {
      error.value = err.response?.data?.message || err.message || 'Error al eliminar usuario';
      console.error('Error deleting user:', err);
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function restoreUser(id: number) {
    try {
      loading.value = true;
      error.value = null;
      
      await userService.restoreUser(id);
      
      // Mover de trashedUsers a users
      const userIndex = trashedUsers.value.findIndex((u: User) => u.id === id);
      if (userIndex !== -1) {
        const restoredUser = trashedUsers.value[userIndex];
        trashedUsers.value.splice(userIndex, 1);
        users.value.unshift({ ...restoredUser, deleted_at: null });
      }
      
    } catch (err: any) {
      error.value = err.response?.data?.message || err.message || 'Error al restaurar usuario';
      console.error('Error restoring user:', err);
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function updatePassword(id: number, data: UpdatePasswordRequest) {
    try {
      loading.value = true;
      error.value = null;
      
      await userService.updatePassword(id, data);
      
    } catch (err: any) {
      error.value = err.response?.data?.message || err.message || 'Error al actualizar contraseña';
      console.error('Error updating password:', err);
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function resetPassword(id: number, data: ResetPasswordRequest) {
    try {
      loading.value = true;
      error.value = null;
      
      await userService.resetPassword(id, data);
      
    } catch (err: any) {
      error.value = err.response?.data?.message || err.message || 'Error al restablecer contraseña';
      console.error('Error resetting password:', err);
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function verifyEmail(id: number) {
    try {
      loading.value = true;
      error.value = null;
      
      await userService.verifyEmail(id);
      
      // Actualizar el estado local
      const userIndex = users.value.findIndex((u: User) => u.id === id);
      if (userIndex !== -1) {
        users.value[userIndex].email_verified_at = new Date().toISOString();
      }
      
      if (currentUser.value?.id === id) {
        currentUser.value.email_verified_at = new Date().toISOString();
      }
      
    } catch (err: any) {
      error.value = err.response?.data?.message || err.message || 'Error al verificar email';
      console.error('Error verifying email:', err);
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function fetchUserStats() {
    try {
      loading.value = true;
      error.value = null;
      userStats.value = await userService.getUserStats();
    } catch (err: any) {
      error.value = err.message || 'Error al cargar estadísticas';
      console.error('Error fetching user stats:', err);
      throw err;
    } finally {
      loading.value = false;
    }
  }

  // Utility functions
  function setSearchQuery(query: string) {
    searchQuery.value = query;
    currentPage.value = 1; // Reset to first page when searching
  }

  function setPage(page: number) {
    if (page >= 1 && page <= totalPages.value) {
      currentPage.value = page;
    }
  }

  function setPerPage(size: number) {
    perPage.value = size;
    currentPage.value = 1; // Reset to first page when changing page size
  }

  function clearError() {
    error.value = null;
  }

  function clearCurrentUser() {
    currentUser.value = null;
  }

  function getUserById(id: number) {
    return users.value.find((user: User) => user.id === id) || null;
  }

  function isEmailVerified(user: User) {
    return !!user.email_verified_at;
  }

  // Reset store
  function $reset() {
    users.value = [];
    activeUsers.value = [];
    trashedUsers.value = [];
    currentUser.value = null;
    userStats.value = null;
    loading.value = false;
    error.value = null;
    currentPage.value = 1;
    perPage.value = 10;
    searchQuery.value = '';
  }

  return {
    // State
    users,
    activeUsers,
    trashedUsers,
    currentUser,
    userStats,
    loading,
    error,
    currentPage,
    perPage,
    searchQuery,

    // Computed
    totalUsers,
    totalPages,
    filteredUsers,
    paginatedUsers,
    hasUsers,
    hasTrashedUsers,

    // Actions
    fetchUsers,
    fetchActiveUsers,
    fetchTrashedUsers,
    fetchUserById,
    createUser,
    updateUser,
    deleteUser,
    restoreUser,
    updatePassword,
    resetPassword,
    verifyEmail,
    fetchUserStats,

    // Utilities
    setSearchQuery,
    setPage,
    setPerPage,
    clearError,
    clearCurrentUser,
    getUserById,
    isEmailVerified,
    $reset
  };
});
