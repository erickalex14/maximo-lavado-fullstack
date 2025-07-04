<template>
  <div class="usuarios-container">
    <!-- Header -->
    <div class="page-header">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">
            Usuarios
          </h1>
          <p class="text-gray-600 mt-1">
            Gestión de usuarios del sistema
          </p>
        </div>
        
        <div class="flex gap-4">
          <button
            @click="showTrashedUsers"
            class="btn btn-outline-secondary"
            :class="{ 'btn-secondary': showingTrash }"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            {{ showingTrash ? 'Ver Activos' : 'Ver Eliminados' }}
            <span v-if="hasTrashedUsers && !showingTrash" class="ml-1 bg-red-100 text-red-800 text-xs font-medium px-2 py-0.5 rounded-full">
              {{ trashedUsers.length }}
            </span>
          </button>
          
          <button
            @click="openCreateModal"
            class="btn btn-primary"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Nuevo Usuario
          </button>
        </div>
      </div>
    </div>

    <!-- Stats Cards -->
    <div v-if="userStats" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
      <div class="stats-card">
        <div class="stats-icon bg-blue-100 text-blue-600">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
          </svg>
        </div>
        <div class="stats-content">
          <p class="stats-label">Total Usuarios</p>
          <p class="stats-value">{{ userStats.total_users }}</p>
        </div>
      </div>

      <div class="stats-card">
        <div class="stats-icon bg-green-100 text-green-600">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <div class="stats-content">
          <p class="stats-label">Verificados</p>
          <p class="stats-value">{{ userStats.verified_users }}</p>
        </div>
      </div>

      <div class="stats-card">
        <div class="stats-icon bg-yellow-100 text-yellow-600">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 15.5c-.77.833.192 2.5 1.732 2.5z" />
          </svg>
        </div>
        <div class="stats-content">
          <p class="stats-label">Sin Verificar</p>
          <p class="stats-value">{{ userStats.unverified_users }}</p>
        </div>
      </div>

      <div class="stats-card">
        <div class="stats-icon bg-purple-100 text-purple-600">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
          </svg>
        </div>
        <div class="stats-content">
          <p class="stats-label">Crecimiento</p>
          <p class="stats-value text-sm">{{ userStats.growth_percentage.toFixed(1) }}%</p>
        </div>
      </div>
    </div>

    <!-- Filters and Search -->
    <div class="filters-section mb-6">
      <div class="flex flex-col md:flex-row gap-4 items-start md:items-center justify-between">
        <div class="flex-1 max-w-md">
          <label class="sr-only">Buscar usuarios</label>
          <div class="relative">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Buscar por nombre o email..."
              class="search-input"
            />
            <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
        </div>
        
        <div class="flex items-center gap-4">
          <div class="flex items-center gap-2">
            <label class="text-sm font-medium text-gray-700">Mostrar:</label>
            <select v-model="perPage" class="form-select text-sm">
              <option :value="10">10</option>
              <option :value="25">25</option>
              <option :value="50">50</option>
              <option :value="100">100</option>
            </select>
          </div>

          <button
            @click="refreshData"
            :disabled="loading"
            class="btn btn-outline-secondary"
          >
            <svg class="w-4 h-4 mr-2" :class="{ 'animate-spin': loading }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Actualizar
          </button>
        </div>
      </div>
    </div>

    <!-- Table -->
    <div class="table-container">
      <div v-if="loading && (!hasUsers && !hasTrashedUsers)" class="loading-container">
        <svg class="animate-spin w-8 h-8 text-blue-600" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <p class="text-gray-600 ml-3">Cargando usuarios...</p>
      </div>

      <div v-else-if="!hasCurrentUsers" class="empty-state">
        <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
        </svg>
        <h3 class="text-lg font-medium text-gray-900 mb-2">
          {{ showingTrash ? 'No hay usuarios eliminados' : 'No hay usuarios registrados' }}
        </h3>
        <p class="text-gray-600 mb-4">
          {{ showingTrash ? 'Todos los usuarios están activos.' : 'Comienza creando el primer usuario del sistema.' }}
        </p>
        <button
          v-if="!showingTrash"
          @click="openCreateModal"
          class="btn btn-primary"
        >
          Crear Primer Usuario
        </button>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="data-table">
          <thead>
            <tr>
              <th>Usuario</th>
              <th>Email</th>
              <th>Estado</th>
              <th>Verificación</th>
              <th>Registro</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="user in paginatedUsers" :key="user.id">
              <td>
                <div class="flex items-center">
                  <div class="user-avatar">
                    {{ user.name.charAt(0).toUpperCase() }}
                  </div>
                  <div class="ml-3">
                    <p class="font-medium text-gray-900">{{ user.name }}</p>
                    <p class="text-sm text-gray-500">ID: {{ user.id }}</p>
                  </div>
                </div>
              </td>
              <td>
                <p class="text-gray-900">{{ user.email }}</p>
              </td>
              <td>
                <span v-if="!showingTrash" class="status-badge status-active">
                  Activo
                </span>
                <span v-else class="status-badge status-inactive">
                  Eliminado
                </span>
              </td>
              <td>
                <div class="flex items-center">
                  <span v-if="isEmailVerified(user)" class="status-badge status-verified">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    Verificado
                  </span>
                  <span v-else class="status-badge status-pending">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                    Pendiente
                  </span>
                </div>
              </td>
              <td>
                <p class="text-gray-900">{{ formatDate(user.created_at) }}</p>
                <p class="text-sm text-gray-500">{{ formatTime(user.created_at) }}</p>
              </td>
              <td>
                <div class="action-buttons">
                  <button
                    @click="openViewModal(user)"
                    class="btn-action btn-action-view"
                    title="Ver detalles"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </button>
                  
                  <button
                    v-if="!showingTrash"
                    @click="openEditModal(user)"
                    class="btn-action btn-action-edit"
                    title="Editar"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </button>

                  <button
                    v-if="!showingTrash && !isEmailVerified(user)"
                    @click="verifyUserEmail(user.id)"
                    class="btn-action btn-action-verify"
                    title="Verificar email"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </button>

                  <button
                    v-if="!showingTrash"
                    @click="openPasswordModal(user)"
                    class="btn-action btn-action-password"
                    title="Cambiar contraseña"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                  </button>
                  
                  <button
                    v-if="!showingTrash"
                    @click="confirmDelete(user)"
                    class="btn-action btn-action-delete"
                    title="Eliminar"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                  
                  <button
                    v-if="showingTrash"
                    @click="confirmRestore(user)"
                    class="btn-action btn-action-restore"
                    title="Restaurar"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="totalPages > 1" class="pagination-container">
      <div class="pagination-info">
        Mostrando {{ ((currentPage - 1) * perPage) + 1 }} a {{ Math.min(currentPage * perPage, filteredUsers.length) }} de {{ filteredUsers.length }} registros
      </div>
      
      <div class="pagination-controls">
        <button
          @click="setPage(currentPage - 1)"
          :disabled="currentPage === 1"
          class="pagination-btn"
        >
          Anterior
        </button>
        
        <button
          v-for="page in visiblePages"
          :key="page"
          @click="setPage(page)"
          :class="[
            'pagination-btn',
            { 'pagination-btn-active': page === currentPage }
          ]"
        >
          {{ page }}
        </button>
        
        <button
          @click="setPage(currentPage + 1)"
          :disabled="currentPage === totalPages"
          class="pagination-btn"
        >
          Siguiente
        </button>
      </div>
    </div>

    <!-- Error Message -->
    <div v-if="error" class="error-banner">
      <div class="flex">
        <svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
        </svg>
        <div class="ml-3">
          <h3 class="text-sm font-medium text-red-800">Error</h3>
          <div class="mt-2 text-sm text-red-700">{{ error }}</div>
        </div>
        <div class="ml-auto pl-3">
          <button @click="clearError" class="error-close-btn">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Modals -->
    <UserModal
      v-model:visible="showUserModal"
      :mode="modalMode"
      :user="selectedUser"
      @saved="handleUserSaved"
    />

    <PasswordModal
      v-model:visible="showPasswordModal"
      :user="selectedUser"
      @saved="handlePasswordChanged"
    />

    <!-- Confirmation Dialogs -->
    <ConfirmDialog
      v-model:visible="showDeleteConfirm"
      title="Eliminar Usuario"
      :message="`¿Estás seguro de que deseas eliminar al usuario '${selectedUser?.name}'?`"
      confirm-text="Eliminar"
      cancel-text="Cancelar"
      type="danger"
      @confirm="handleDelete"
    />

    <ConfirmDialog
      v-model:visible="showRestoreConfirm"
      title="Restaurar Usuario"
      :message="`¿Estás seguro de que deseas restaurar al usuario '${selectedUser?.name}'?`"
      confirm-text="Restaurar"
      cancel-text="Cancelar"
      type="success"
      @confirm="handleRestore"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { useUserStore } from '@/stores/user';
import type { User } from '@/types';

// Modales (se crearán después)
import UserModal from './UserModal.vue';
import PasswordModal from './PasswordModal.vue';
import ConfirmDialog from '@/components/shared/ConfirmDialog.vue';

// Store
const userStore = useUserStore();

// State
const showingTrash = ref(false);
const showUserModal = ref(false);
const showPasswordModal = ref(false);
const showDeleteConfirm = ref(false);
const showRestoreConfirm = ref(false);
const modalMode = ref<'create' | 'edit' | 'view'>('create');
const selectedUser = ref<User | null>(null);

// Computed
const users = computed(() => userStore.users);
const trashedUsers = computed(() => userStore.trashedUsers);
const userStats = computed(() => userStore.userStats);
const loading = computed(() => userStore.loading);
const error = computed(() => userStore.error);
const hasUsers = computed(() => userStore.hasUsers);
const hasTrashedUsers = computed(() => userStore.hasTrashedUsers);
const filteredUsers = computed(() => userStore.filteredUsers);
const paginatedUsers = computed(() => userStore.paginatedUsers);
const currentPage = computed(() => userStore.currentPage);
const totalPages = computed(() => userStore.totalPages);
const perPage = computed({
  get: () => userStore.perPage,
  set: (value) => userStore.setPerPage(value)
});
const searchQuery = computed({
  get: () => userStore.searchQuery,
  set: (value) => userStore.setSearchQuery(value)
});

const hasCurrentUsers = computed(() => {
  return showingTrash.value ? hasTrashedUsers.value : hasUsers.value;
});

const visiblePages = computed(() => {
  const delta = 2;
  const range = [];
  const rangeWithDots = [];

  for (let i = Math.max(2, currentPage.value - delta); i <= Math.min(totalPages.value - 1, currentPage.value + delta); i++) {
    range.push(i);
  }

  if (currentPage.value - delta > 2) {
    rangeWithDots.push(1, '...');
  } else {
    rangeWithDots.push(1);
  }

  rangeWithDots.push(...range);

  if (currentPage.value + delta < totalPages.value - 1) {
    rangeWithDots.push('...', totalPages.value);
  } else {
    rangeWithDots.push(totalPages.value);
  }

  return rangeWithDots.filter((item, index, arr) => arr.indexOf(item) === index);
});

// Methods
async function loadData() {
  try {
    await Promise.all([
      userStore.fetchUsers(),
      userStore.fetchTrashedUsers(),
      userStore.fetchUserStats()
    ]);
  } catch (error) {
    console.error('Error loading user data:', error);
  }
}

async function refreshData() {
  await loadData();
}

function showTrashedUsers() {
  showingTrash.value = !showingTrash.value;
  userStore.setSearchQuery('');
  userStore.setPage(1);
}

function openCreateModal() {
  selectedUser.value = null;
  modalMode.value = 'create';
  showUserModal.value = true;
}

function openEditModal(user: User) {
  selectedUser.value = user;
  modalMode.value = 'edit';
  showUserModal.value = true;
}

function openViewModal(user: User) {
  selectedUser.value = user;
  modalMode.value = 'view';
  showUserModal.value = true;
}

function openPasswordModal(user: User) {
  selectedUser.value = user;
  showPasswordModal.value = true;
}

function confirmDelete(user: User) {
  selectedUser.value = user;
  showDeleteConfirm.value = true;
}

function confirmRestore(user: User) {
  selectedUser.value = user;
  showRestoreConfirm.value = true;
}

async function handleDelete() {
  if (!selectedUser.value) return;
  
  try {
    await userStore.deleteUser(selectedUser.value.id);
    showDeleteConfirm.value = false;
  } catch (error) {
    console.error('Error deleting user:', error);
  }
}

async function handleRestore() {
  if (!selectedUser.value) return;
  
  try {
    await userStore.restoreUser(selectedUser.value.id);
    showRestoreConfirm.value = false;
  } catch (error) {
    console.error('Error restoring user:', error);
  }
}

async function verifyUserEmail(userId: number) {
  try {
    await userStore.verifyEmail(userId);
  } catch (error) {
    console.error('Error verifying email:', error);
  }
}

function handleUserSaved() {
  showUserModal.value = false;
  refreshData();
}

function handlePasswordChanged() {
  showPasswordModal.value = false;
}

function setPage(page: number | string) {
  if (typeof page === 'number') {
    userStore.setPage(page);
  }
}

function clearError() {
  userStore.clearError();
}

function formatDate(dateString: string): string {
  return new Date(dateString).toLocaleDateString('es-CO');
}

function formatTime(dateString: string): string {
  return new Date(dateString).toLocaleTimeString('es-CO', {
    hour: '2-digit',
    minute: '2-digit'
  });
}

function isEmailVerified(user: User): boolean {
  return userStore.isEmailVerified(user);
}

// Watchers
watch(showingTrash, () => {
  userStore.setPage(1);
});

// Lifecycle
onMounted(() => {
  loadData();
});
</script>

<style scoped>
/* Contenedor principal */
.usuarios-container {
  min-height: 100vh;
  background-color: #f9fafb;
  padding: 1.5rem;
}

.page-header {
  margin-bottom: 2rem;
}

/* Stats Cards */
.stats-card {
  background: white;
  border-radius: 0.5rem;
  padding: 1.5rem;
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
  border: 1px solid #e5e7eb;
  display: flex;
  align-items: center;
  gap: 1rem;
}

.stats-icon {
  width: 3rem;
  height: 3rem;
  border-radius: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.stats-content {
  flex: 1;
}

.stats-label {
  font-size: 0.875rem;
  color: #6b7280;
  margin-bottom: 0.25rem;
}

.stats-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: #111827;
}

/* Filtros */
.filters-section {
  background: white;
  border-radius: 0.5rem;
  padding: 1.5rem;
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
  border: 1px solid #e5e7eb;
}

.search-input {
  width: 100%;
  padding: 0.5rem 0.75rem 0.5rem 2.5rem;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  background-color: white;
  color: #111827;
}

.search-input:focus {
  outline: none;
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5);
  border-color: #3b82f6;
}

.search-icon {
  position: absolute;
  left: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  width: 1rem;
  height: 1rem;
  color: #9ca3af;
}

.form-select {
  padding: 0.375rem 2rem 0.375rem 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  background-color: white;
  color: #111827;
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
  background-position: right 0.5rem center;
  background-repeat: no-repeat;
  background-size: 1.5rem 1.5rem;
}

/* Tabla */
.table-container {
  background: white;
  border-radius: 0.5rem;
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
  border: 1px solid #e5e7eb;
  overflow: hidden;
}

.loading-container {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 3rem;
}

.empty-state {
  text-align: center;
  padding: 3rem;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table th {
  background-color: #f9fafb;
  padding: 0.75rem 1rem;
  text-align: left;
  font-weight: 500;
  color: #374151;
  border-bottom: 1px solid #e5e7eb;
}

.data-table td {
  padding: 1rem;
  border-bottom: 1px solid #e5e7eb;
  vertical-align: middle;
}

.data-table tr:hover {
  background-color: #f9fafb;
}

.user-avatar {
  width: 2.5rem;
  height: 2.5rem;
  border-radius: 50%;
  background-color: #3b82f6;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 1rem;
}

/* Status badges */
.status-badge {
  display: inline-flex;
  align-items: center;
  padding: 0.25rem 0.5rem;
  border-radius: 0.375rem;
  font-size: 0.75rem;
  font-weight: 500;
}

.status-active {
  background-color: #d1fae5;
  color: #065f46;
}

.status-inactive {
  background-color: #fee2e2;
  color: #991b1b;
}

.status-verified {
  background-color: #dbeafe;
  color: #1e40af;
}

.status-pending {
  background-color: #fef3c7;
  color: #92400e;
}

/* Action buttons */
.action-buttons {
  display: flex;
  gap: 0.5rem;
}

.btn-action {
  padding: 0.375rem;
  border-radius: 0.375rem;
  border: 1px solid transparent;
  transition: all 0.2s;
  cursor: pointer;
}

.btn-action:hover {
  transform: translateY(-1px);
}

.btn-action-view {
  color: #6b7280;
  border-color: #d1d5db;
}

.btn-action-view:hover {
  background-color: #f3f4f6;
  border-color: #9ca3af;
}

.btn-action-edit {
  color: #3b82f6;
  border-color: #93c5fd;
}

.btn-action-edit:hover {
  background-color: #dbeafe;
  border-color: #3b82f6;
}

.btn-action-verify {
  color: #10b981;
  border-color: #86efac;
}

.btn-action-verify:hover {
  background-color: #d1fae5;
  border-color: #10b981;
}

.btn-action-password {
  color: #f59e0b;
  border-color: #fcd34d;
}

.btn-action-password:hover {
  background-color: #fef3c7;
  border-color: #f59e0b;
}

.btn-action-delete {
  color: #ef4444;
  border-color: #fca5a5;
}

.btn-action-delete:hover {
  background-color: #fee2e2;
  border-color: #ef4444;
}

.btn-action-restore {
  color: #10b981;
  border-color: #86efac;
}

.btn-action-restore:hover {
  background-color: #d1fae5;
  border-color: #10b981;
}

/* Paginación */
.pagination-container {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: 1.5rem;
  padding: 1rem 1.5rem;
  background: white;
  border-radius: 0.5rem;
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
  border: 1px solid #e5e7eb;
}

.pagination-info {
  color: #6b7280;
  font-size: 0.875rem;
}

.pagination-controls {
  display: flex;
  gap: 0.5rem;
}

.pagination-btn {
  padding: 0.5rem 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  background-color: white;
  color: #374151;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.2s;
}

.pagination-btn:hover:not(:disabled) {
  background-color: #f9fafb;
  border-color: #9ca3af;
}

.pagination-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.pagination-btn-active {
  background-color: #3b82f6;
  border-color: #3b82f6;
  color: white;
}

/* Botones generales */
.btn {
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
  font-weight: 500;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  border: none;
  cursor: pointer;
}

.btn-primary {
  background-color: #3b82f6;
  color: white;
}

.btn-primary:hover {
  background-color: #2563eb;
}

.btn-secondary {
  background-color: #6b7280;
  color: white;
}

.btn-secondary:hover {
  background-color: #4b5563;
}

.btn-outline-secondary {
  border: 1px solid #d1d5db;
  color: #374151;
  background-color: white;
}

.btn-outline-secondary:hover {
  background-color: #f9fafb;
}

/* Error banner */
.error-banner {
  margin-top: 1.5rem;
  background-color: #fee2e2;
  border: 1px solid #fecaca;
  border-radius: 0.5rem;
  padding: 1rem;
}

.error-close-btn {
  background-color: #fee2e2;
  border-radius: 0.375rem;
  padding: 0.375rem;
  color: #dc2626;
  cursor: pointer;
  transition: background-color 0.2s;
}

.error-close-btn:hover {
  background-color: #fecaca;
}
</style>
