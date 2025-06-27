<?php

namespace App\Contracts;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface
{
    public function getAll(): Collection;
    public function findById(int $id): ?User;
    public function create(array $data): User;
    public function update(int $id, array $data): ?User;
    public function delete(int $id): bool;
    public function findByEmail(string $email): ?User;
    public function getActiveUsers(): Collection;
    public function getUsersCreatedBetween(string $fechaInicio, string $fechaFin): Collection;
    public function updatePassword(int $id, string $password): bool;
    public function getUserStats(): array;
}
