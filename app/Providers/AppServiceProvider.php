<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{

    //Registrar los servicios de la aplicación

    public function register(): void
    {
        // Registrar repositories
        $this->app->bind(
            \App\Contracts\ClienteRepositoryInterface::class,
            \App\Repositories\ClienteRepository::class
        );

        $this->app->bind(
            \App\Contracts\LavadoRepositoryInterface::class,
            \App\Repositories\LavadoRepository::class
        );

        $this->app->bind(
            \App\Contracts\VehiculoRepositoryInterface::class,
            \App\Repositories\VehiculoRepository::class
        );

        $this->app->bind(
            \App\Contracts\EmpleadoRepositoryInterface::class,
            \App\Repositories\EmpleadoRepository::class
        );

        $this->app->bind(
            \App\Contracts\ProductoAutomotrizRepositoryInterface::class,
            \App\Repositories\ProductoAutomotrizRepository::class
        );

        $this->app->bind(
            \App\Contracts\ProductoDespensaRepositoryInterface::class,
            \App\Repositories\ProductoDespensaRepository::class
        );

        $this->app->bind(
            \App\Contracts\ProveedorRepositoryInterface::class,
            \App\Repositories\ProveedorRepository::class
        );

        $this->app->bind(
            \App\Contracts\IngresoRepositoryInterface::class,
            \App\Repositories\IngresoRepository::class
        );

        $this->app->bind(
            \App\Contracts\EgresoRepositoryInterface::class,
            \App\Repositories\EgresoRepository::class
        );

        $this->app->bind(
            \App\Contracts\GastoGeneralRepositoryInterface::class,
            \App\Repositories\GastoGeneralRepository::class
        );

        $this->app->bind(
            \App\Contracts\FacturaRepositoryInterface::class,
            \App\Repositories\FacturaRepository::class
        );

        // Repositorio consolidado de ventas (reemplaza VentaProductoAutomotriz y VentaProductoDespensa)
        $this->app->bind(
            \App\Contracts\VentaRepositoryInterface::class,
            \App\Repositories\VentaRepository::class
        );

        $this->app->bind(
            \App\Contracts\VentaDetalleRepositoryInterface::class,
            \App\Repositories\VentaDetalleRepository::class
        );

        $this->app->bind(
            \App\Contracts\ReporteRepositoryInterface::class,
            \App\Repositories\ReporteRepository::class
        );

        $this->app->bind(
            \App\Contracts\UserRepositoryInterface::class,
            \App\Repositories\UserRepository::class
        );

        $this->app->bind(
            \App\Contracts\BalanceRepositoryInterface::class,
            \App\Repositories\BalanceRepository::class
        );

        $this->app->bind(
            \App\Contracts\DashboardRepositoryInterface::class,
            \App\Repositories\DashboardRepository::class
        );

        // 🚗 Tipos de Vehículos y Servicios (FALTABAN ESTOS)
        $this->app->bind(
            \App\Contracts\TipoVehiculoRepositoryInterface::class,
            \App\Repositories\TipoVehiculoRepository::class
        );

        $this->app->bind(
            \App\Contracts\ServicioRepositoryInterface::class,
            \App\Repositories\ServicioRepository::class
        );

        // 🧾 Facturación Electrónica SRI
        $this->app->bind(
            \App\Contracts\FacturaElectronicaRepositoryInterface::class,
            \App\Repositories\FacturaElectronicaRepository::class
        );

        $this->app->bind(
            \App\Contracts\FacturaDetalleRepositoryInterface::class,
            \App\Repositories\FacturaDetalleRepository::class
        );
    }

    //Bootstrap los servicios de la aplicación

    public function boot(): void
    {
        //
    }
}
