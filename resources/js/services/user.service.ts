import apiService from './api';
import type { 
  User, 
  CreateUserForm, 
  UpdateUserForm, 
  UpdatePasswordForm, 
  ResetPasswordForm,
  UserStats 
} from '@/types';

export interface CreateUserRequest extends CreateUserForm {}
export interface UpdateUserRequest extends UpdateUserForm {}
export interface UpdatePasswordRequest extends UpdatePasswordForm {}
export interface ResetPasswordRequest extends ResetPasswordForm {}

export interface ApiResponse<T> {
  status: string;
  data: T;
  message?: string;
}

export class UserService {
  private baseUrl = '/api/usuarios';

  // Obtener todos los usuarios
  async getUsers(): Promise<User[]> {
    const response = await apiService.get<ApiResponse<User[]>>(this.baseUrl);
    return response.data?.data || [];
  }

  // Obtener usuarios activos
  async getActiveUsers(): Promise<User[]> {
    const response = await apiService.get<ApiResponse<User[]>>(`${this.baseUrl}/activos`);
    return response.data?.data || [];
  }

  // Obtener usuarios eliminados
  async getTrashedUsers(): Promise<User[]> {
    const response = await apiService.get<ApiResponse<User[]>>(`${this.baseUrl}/trashed`);
    return response.data?.data || [];
  }

  // Obtener un usuario por ID
  async getUserById(id: number): Promise<User> {
    const response = await apiService.get<ApiResponse<User>>(`${this.baseUrl}/${id}`);
    return response.data?.data!;
  }

  // Crear nuevo usuario
  async createUser(data: CreateUserRequest): Promise<User> {
    const response = await apiService.post<ApiResponse<User>>(this.baseUrl, data);
    return response.data?.data!;
  }

  // Actualizar usuario
  async updateUser(id: number, data: UpdateUserRequest): Promise<User> {
    const response = await apiService.put<ApiResponse<User>>(`${this.baseUrl}/${id}`, data);
    return response.data?.data!;
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
    return response.data?.data!;
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

    if (!data.current_password?.trim()) {
      errors.current_password = 'La contraseña actual es requerida';
    }

    if (!data.new_password?.trim()) {
      errors.new_password = 'La nueva contraseña es requerida';
    } else if (data.new_password.length < 8) {
      errors.new_password = 'La nueva contraseña debe tener al menos 8 caracteres';
    }

    if (data.new_password !== data.new_password_confirmation) {
      errors.new_password_confirmation = 'Las contraseñas no coinciden';
    }

    return {
      valid: Object.keys(errors).length === 0,
      errors
    };
  }
}

export const userService = new UserService();
export default userService;
