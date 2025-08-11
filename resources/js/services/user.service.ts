import apiService from './api';
import type { 
  User, 
  CreateUserForm, 
  UpdateUserForm,
  UserStats 
} from '@/types';

export interface CreateUserRequest extends CreateUserForm {}
export interface UpdateUserRequest extends UpdateUserForm {}
export interface UpdatePasswordRequest { current_password: string; new_password: string; new_password_confirmation: string; }
export interface ResetPasswordRequest { new_password: string; new_password_confirmation: string; }

export interface ApiResponse<T> {
  status: string;
  data: T;
  message?: string;
}

export class UserService {
  private baseUrl = '/usuarios';

  // Obtener todos los usuarios
  async getUsers(): Promise<User[]> {
  const response = await apiService.get<ApiResponse<User[]>>(this.baseUrl);
  console.debug('[UserService] getUsers raw', response);
  return (response as any)?.data || [];
  }

  // Obtener usuarios activos
  async getActiveUsers(): Promise<User[]> {
  const response = await apiService.get<ApiResponse<User[]>>(`${this.baseUrl}/activos`);
  console.debug('[UserService] getActiveUsers raw', response);
  return (response as any)?.data || [];
  }

  // Obtener usuarios eliminados
  async getTrashedUsers(): Promise<User[]> {
  const response = await apiService.get<ApiResponse<User[]>>(`${this.baseUrl}/trashed`);
  console.debug('[UserService] getTrashedUsers raw', response);
  return (response as any)?.data || [];
  }

  // Obtener un usuario por ID
  async getUserById(id: number): Promise<User> {
  const response = await apiService.get<ApiResponse<User>>(`${this.baseUrl}/${id}`);
  console.debug('[UserService] getUserById raw', response);
  return (response as any)?.data!;
  }

  // Crear nuevo usuario
  async createUser(data: CreateUserRequest): Promise<User> {
  const response = await apiService.post<ApiResponse<User>>(this.baseUrl, data);
  console.debug('[UserService] createUser raw', response);
  return (response as any)?.data!;
  }

  // Actualizar usuario
  async updateUser(id: number, data: UpdateUserRequest): Promise<User> {
  const response = await apiService.put<ApiResponse<User>>(`${this.baseUrl}/${id}`, data);
  console.debug('[UserService] updateUser raw', response);
  return (response as any)?.data!;
  }

  // Eliminar usuario (soft delete)
  async deleteUser(id: number): Promise<void> {
    await apiService.delete(`${this.baseUrl}/${id}`);
  }

  // Restaurar usuario eliminado
  async restoreUser(id: number): Promise<void> {
    await apiService.put(`${this.baseUrl}/${id}/restore`);
  }

  // Actualizar contraseña
  async updatePassword(id: number, data: UpdatePasswordRequest): Promise<void> {
    await apiService.put(`${this.baseUrl}/${id}/password`, data);
  }

  // Restablecer contraseña (admin)
  async resetPassword(id: number, data: ResetPasswordRequest): Promise<void> {
    await apiService.put(`${this.baseUrl}/${id}/reset-password`, data);
  }

  // Verificar email
  async verifyEmail(id: number): Promise<void> {
    await apiService.put(`${this.baseUrl}/${id}/verify-email`);
  }

  // Obtener estadísticas
  async getUserStats(): Promise<UserStats> {
    const response = await apiService.get<ApiResponse<UserStats>>(`${this.baseUrl}/estadisticas`);
    console.debug('[UserService] getUserStats raw', response);
    const raw: any = (response as any)?.data || {};
    // Normalizar posibles nombres diferentes
    return {
      total_users: raw.total_users ?? raw.total ?? 0,
      active_users: raw.active_users ?? raw.activos ?? raw.verified_users ?? 0,
      verified_users: raw.verified_users ?? raw.active_users ?? 0,
      unverified_users: raw.unverified_users ?? (raw.total_users && raw.active_users != null ? raw.total_users - raw.active_users : 0),
      deleted_users: raw.deleted_users ?? raw.eliminados ?? 0,
      users_this_month: raw.users_this_month ?? raw.mes_actual ?? 0,
      users_last_month: raw.users_last_month ?? raw.mes_anterior ?? 0,
      growth_percentage: raw.growth_percentage ?? raw.verification_rate ?? 0
    } as UserStats;
  }

  // Validaciones locales
  validateUser(data: CreateUserRequest | UpdateUserRequest): { valid: boolean; errors: Record<string, string> } {
    const errors: Record<string, string> = {};

    if ('name' in data && data.name !== undefined) {
      if (!data.name?.trim()) {
        errors.name = 'El nombre es requerido';
      } else if (data.name.length > 255) {
        errors.name = 'El nombre no puede exceder 255 caracteres';
      }
    }

    if ('email' in data && data.email !== undefined) {
      if (!data.email?.trim()) {
        errors.email = 'El email es requerido';
      } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(data.email)) {
        errors.email = 'El email no es válido';
      } else if (data.email.length > 255) {
        errors.email = 'El email no puede exceder 255 caracteres';
      }
    }

    if ('password' in data && data.password !== undefined) {
      if (!data.password?.trim()) {
        errors.password = 'La contraseña es requerida';
      } else if (data.password.length < 8) {
        errors.password = 'La contraseña debe tener al menos 8 caracteres';
      }

      if ('password_confirmation' in data && data.password !== data.password_confirmation) {
        errors.password_confirmation = 'Las contraseñas no coinciden';
      }
    }

    return {
      valid: Object.keys(errors).length === 0,
      errors
    };
  }

  validatePassword(data: UpdatePasswordRequest): { valid: boolean; errors: Record<string, string> } {
    const errors: Record<string, string> = {};
    if (!data.current_password || !data.current_password.trim()) errors.current_password = 'La contraseña actual es requerida';
    if (!data.new_password || data.new_password.length < 8) errors.new_password = 'La nueva contraseña debe tener al menos 8 caracteres';
    if (data.new_password !== data.new_password_confirmation) errors.new_password_confirmation = 'Las contraseñas no coinciden';
    return { valid: Object.keys(errors).length === 0, errors };
  }
}

export const userService = new UserService();
export default userService;
