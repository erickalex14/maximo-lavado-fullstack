<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use App\Http\Requests\User\CreateUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Requests\User\UpdatePasswordRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    // Lista de usuarios

    public function index(): JsonResponse
    {
        try {
            $users = $this->userService->getAllUsers();
            
            return response()->json([
                'status' => 'success',
                'data' => $users
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener los usuarios: ' . $e->getMessage()
            ], 500);
        }
    }

    // Guarda un nuevo usuario

    public function store(CreateUserRequest $request): JsonResponse
    {
        try {
            $user = $this->userService->createUser($request->validated());
            
            return response()->json([
                'status' => 'success',
                'message' => 'Usuario creado correctamente',
                'data' => $user->only(['id', 'name', 'email', 'created_at'])
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al crear el usuario: ' . $e->getMessage()
            ], 500);
        }
    }

    // Obtiene un usuario específico por ID

    public function show(int $id): JsonResponse
    {
        try {
            $user = $this->userService->getUserProfile($id);
            
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Usuario no encontrado'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener el usuario: ' . $e->getMessage()
            ], 500);
        }
    }

    // Actualiza un usuario específico por ID
    
    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        try {
            $user = $this->userService->updateUser($id, $request->validated());
            
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Usuario no encontrado'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Usuario actualizado correctamente',
                'data' => $user->only(['id', 'name', 'email', 'email_verified_at', 'updated_at'])
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al actualizar el usuario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified user.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $deleted = $this->userService->deleteUser($id);
            
            if (!$deleted) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Usuario no encontrado'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Usuario eliminado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al eliminar el usuario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore a soft deleted user.
     */
    public function restore(int $id): JsonResponse
    {
        try {
            $restored = $this->userService->restoreUser($id);
            
            if (!$restored) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Usuario no encontrado en la papelera'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Usuario restaurado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al restaurar el usuario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all soft deleted users.
     */
    public function trashed(): JsonResponse
    {
        try {
            $users = $this->userService->getTrashedUsers();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Usuarios eliminados obtenidos correctamente',
                'data' => $users
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener usuarios eliminados: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get active users.
     */
    public function getActiveUsers(): JsonResponse
    {
        try {
            $users = $this->userService->getActiveUsers();
            
            return response()->json([
                'status' => 'success',
                'data' => $users
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener usuarios activos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user password.
     */
    public function updatePassword(UpdatePasswordRequest $request, int $id): JsonResponse
    {
        try {
            $updated = $this->userService->updatePassword(
                $id,
                $request->current_password,
                $request->new_password
            );
            
            if (!$updated) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No se pudo actualizar la contraseña'
                ], 400);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Contraseña actualizada correctamente'
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al actualizar la contraseña: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reset user password (admin action).
     */
    public function resetPassword(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'new_password' => 'required|string|min:8|confirmed'
        ]);

        try {
            $updated = $this->userService->resetPassword($id, $request->new_password);
            
            if (!$updated) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Usuario no encontrado'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Contraseña restablecida correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al restablecer la contraseña: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify user email.
     */
    public function verifyEmail(int $id): JsonResponse
    {
        try {
            $verified = $this->userService->verifyEmail($id);
            
            if (!$verified) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Usuario no encontrado'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Email verificado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al verificar el email: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user statistics.
     */
    public function getStats(): JsonResponse
    {
        try {
            $stats = $this->userService->getUserStats();
            
            return response()->json([
                'status' => 'success',
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener estadísticas: ' . $e->getMessage()
            ], 500);
        }
    }
}
