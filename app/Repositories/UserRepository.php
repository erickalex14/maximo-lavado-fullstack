<?php

namespace App\Repositories;

use App\Contracts\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UserRepository implements UserRepositoryInterface
{
    public function getAll(): Collection
    {
        return User::select('id', 'name', 'email', 'email_verified_at', 'created_at', 'updated_at')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findById(int $id): ?User
    {
        return User::select('id', 'name', 'email', 'email_verified_at', 'created_at', 'updated_at')
            ->find($id);
    }

    public function create(array $data): User
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
        
        // Verificar email automáticamente para usuarios creados por admin
        if (!isset($data['email_verified_at'])) {
            $data['email_verified_at'] = now();
        }

        return User::create($data);
    }

    public function update(int $id, array $data): ?User
    {
        $user = User::find($id);
        if (!$user) {
            return null;
        }

        // No actualizar la contraseña si no se proporciona
        if (isset($data['password']) && empty($data['password'])) {
            unset($data['password']);
        } elseif (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);
        return $user->fresh(['id', 'name', 'email', 'email_verified_at', 'created_at', 'updated_at']);
    }

    public function delete(int $id): bool
    {
        $user = User::find($id);
        return $user ? $user->delete() : false;
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function getActiveUsers(): Collection
    {
        return User::whereNotNull('email_verified_at')
            ->select('id', 'name', 'email', 'email_verified_at', 'created_at')
            ->orderBy('name')
            ->get();
    }

    public function getUsersCreatedBetween(string $fechaInicio, string $fechaFin): Collection
    {
        return User::whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->select('id', 'name', 'email', 'email_verified_at', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function updatePassword(int $id, string $password): bool
    {
        $user = User::find($id);
        if (!$user) {
            return false;
        }

        return $user->update([
            'password' => Hash::make($password)
        ]);
    }

    public function getUserStats(): array
    {
        $totalUsers = User::count();
        $activeUsers = User::whereNotNull('email_verified_at')->count();
        $usersThisMonth = User::where('created_at', '>=', Carbon::now()->startOfMonth())->count();
        $usersToday = User::whereDate('created_at', Carbon::today())->count();

        return [
            'total_users' => $totalUsers,
            'active_users' => $activeUsers,
            'inactive_users' => $totalUsers - $activeUsers,
            'users_this_month' => $usersThisMonth,
            'users_today' => $usersToday,
            'verification_rate' => $totalUsers > 0 ? round(($activeUsers / $totalUsers) * 100, 2) : 0
        ];
    }
}
