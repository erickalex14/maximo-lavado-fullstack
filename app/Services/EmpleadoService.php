<?php

namespace App\Services;

use App\Contracts\EmpleadoRepositoryInterface;
use App\Models\Empleado;
use Illuminate\Database\Eloquent\Collection;

class EmpleadoService
{
    protected $empleadoRepository;

    public function __construct(EmpleadoRepositoryInterface $empleadoRepository)
    {
        $this->empleadoRepository = $empleadoRepository;
    }

    public function getAllEmpleados(): Collection
    {
        return $this->empleadoRepository->getAll();
    }

    public function getEmpleadosWithLavados(): Collection
    {
        return $this->empleadoRepository->getEmpleadosWithLavados();
    }

    public function findEmpleadoById(int $id): ?Empleado
    {
        return $this->empleadoRepository->findById($id);
    }

    public function createEmpleado(array $data): Empleado
    {
        return $this->empleadoRepository->create($data);
    }

    public function updateEmpleado(int $id, array $data): ?Empleado
    {
        return $this->empleadoRepository->update($id, $data);
    }

    public function deleteEmpleado(int $id): bool
    {
        return $this->empleadoRepository->delete($id);
    }

    public function getLavadosPorDia(int $empleadoId, string $fecha): int
    {
        return $this->empleadoRepository->countLavadosByEmpleadoAndDate($empleadoId, $fecha);
    }

    public function getLavadosPorSemana(int $empleadoId, string $fecha): int
    {
        return $this->empleadoRepository->countLavadosByEmpleadoAndWeek($empleadoId, $fecha);
    }

    public function getLavadosPorMes(int $empleadoId, int $anio, int $mes): int
    {
        return $this->empleadoRepository->countLavadosByEmpleadoAndMonth($empleadoId, $anio, $mes);
    }
}
