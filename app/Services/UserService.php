<?php

namespace App\Services;

use App\Contracts\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;

class UserService
{
    protected $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function getAllUsers(): Collection
    {
        return $this->userRepository->getAll();
    }

    public function findUserById(int $id): ?User
    {
        return $this->userRepository->findById($id);
    }

    public function createUser(array $data): User
    {
        // Validar que el email no exista
        if ($this->userRepository->findByEmail($data['email'])) {
            throw new \InvalidArgumentException('El email ya está registrado');
        }

        return $this->userRepository->create($data);
    }

    public function updateUser(int $id, array $data): ?User
    {
        $user = $this->userRepository->findById($id);
        if (!$user) {
            return null;
        }

        // Validar email único si se está actualizando
        if (isset($data['email']) && $data['email'] !== $user->email) {
            $existingUser = $this->userRepository->findByEmail($data['email']);
            if ($existingUser && $existingUser->id !== $id) {
                throw new \InvalidArgumentException('El email ya está registrado por otro usuario');
            }
        }

        return $this->userRepository->update($id, $data);
    }

    public function deleteUser(int $id): bool
    {
        $user = $this->userRepository->findById($id);
        if (!$user) {
            return false;
        }

        // Validar que no sea el último usuario admin (si tienes roles)
        // Aquí puedes agregar lógica adicional según tus necesidades
        
        return $this->userRepository->delete($id);
    }

    public function getActiveUsers(): Collection
    {
        return $this->userRepository->getActiveUsers();
    }

    public function getUsersCreatedBetween(string $fechaInicio, string $fechaFin): Collection
    {
        return $this->userRepository->getUsersCreatedBetween($fechaInicio, $fechaFin);
    }

    public function updatePassword(int $id, string $currentPassword, string $newPassword): bool
    {
        $user = $this->userRepository->findById($id);
        if (!$user) {
            throw new \InvalidArgumentException('Usuario no encontrado');
        }

        // Verificar contraseña actual
        if (!Hash::check($currentPassword, $user->password)) {
            throw new \InvalidArgumentException('La contraseña actual es incorrecta');
        }

        return $this->userRepository->updatePassword($id, $newPassword);
    }

    public function resetPassword(int $id, string $newPassword): bool
    {
        $user = $this->userRepository->findById($id);
        if (!$user) {
            return false;
        }

        return $this->userRepository->updatePassword($id, $newPassword);
    }

    public function getUserStats(): array
    {
        return $this->userRepository->getUserStats();
    }

    public function verifyEmail(int $id): bool
    {
        $user = $this->userRepository->findById($id);
        if (!$user) {
            return false;
        }

        return $this->userRepository->update($id, [
            'email_verified_at' => now()
        ]) !== null;
    }

    public function findByEmail(string $email): ?User
    {
        return $this->userRepository->findByEmail($email);
    }

    public function getUserProfile(int $id): ?array
    {
        $user = $this->userRepository->findById($id);
        if (!$user) {
            return null;
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'email_verified' => $user->email_verified_at !== null,
            'email_verified_at' => $user->email_verified_at,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ];
    }
}
