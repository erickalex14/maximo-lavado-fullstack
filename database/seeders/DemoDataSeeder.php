<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\Vehiculo;
use App\Models\ProductoAutomotriz;
use App\Models\ProductoDespensa;
use App\Models\Proveedor;
use App\Models\VentaProductoAutomotriz;
use App\Models\VentaProductoDespensa;
use App\Models\PagoProveedor;
use App\Models\Lavado;
use App\Models\Ingreso;
use App\Models\Egreso;
use App\Models\GastoGeneral;
use Carbon\Carbon;

class DemoDataSeeder extends Seeder
{
    public function run()
    {
        // Crear usuario administrador (solo si no existe)
        $admin = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Admin Prueba',
                'password' => bcrypt('password123'),
                'email_verified_at' => now(),
            ]
        );

        // Verificar si ya existen datos, si es así, no crear duplicados
        if (Cliente::count() > 0 || Empleado::count() > 0) {
            $this->command->info('Los datos ya existen. Saltando seeder...');
            return;
        }

        // Crear empleados
        $empleados = [
            [
                'nombres' => 'Juan Carlos',
                'apellidos' => 'Pérez',
                'telefono' => '555-0101',
                'cedula' => '12345678',
                'tipo_salario' => 'quincenal',
                'salario' => 400000,
            ],
            [
                'nombres' => 'María José',
                'apellidos' => 'González',
                'telefono' => '555-0102',
                'cedula' => '87654321',
                'tipo_salario' => 'mensual',
                'salario' => 800000,
            ],
            [
                'nombres' => 'Pedro Luis',
                'apellidos' => 'Martínez',
                'telefono' => '555-0103',
                'cedula' => '11223344',
                'tipo_salario' => 'semanal',
                'salario' => 200000,
            ]
        ];

        foreach ($empleados as $empleadoData) {
            Empleado::create($empleadoData);
        }

        // Crear clientes
        $clientes = [
            [
                'nombre' => 'Carlos Alberto Ramírez',
                'telefono' => '555-1001',
                'email' => 'carlos@email.com',
                'direccion' => 'Carrera 15 #45-67',
                'cedula' => '12345001',
            ],
            [
                'nombre' => 'Ana María López',
                'telefono' => '555-1002',
                'email' => 'ana@email.com',
                'direccion' => 'Calle 30 #12-34',
                'cedula' => '87654002',
            ],
            [
                'nombre' => 'Roberto Silva',
                'telefono' => '555-1003',
                'email' => 'roberto@email.com',
                'direccion' => 'Avenida 80 #22-45',
                'cedula' => '11223003',
            ],
            [
                'nombre' => 'Diana Patricia Herrera',
                'telefono' => '555-1004',
                'email' => 'diana@email.com',
                'direccion' => 'Transversal 25 #56-78',
                'cedula' => '55667004',
            ]
        ];

        $clientes_creados = [];
        foreach ($clientes as $clienteData) {
            $clientes_creados[] = Cliente::create($clienteData);
        }

        // Crear vehículos (solo campos que existen en la migración)
        $vehiculos = [
            [
                'cliente_id' => 1,
                'matricula' => 'ABC123',
                'tipo' => 'auto_pequeno',
                'descripcion' => 'Toyota Corolla 2020 Blanco',
            ],
            [
                'cliente_id' => 1,
                'matricula' => 'DEF456',
                'tipo' => 'auto_pequeno',
                'descripcion' => 'Chevrolet Spark 2019 Rojo',
            ],
            [
                'cliente_id' => 2,
                'matricula' => 'GHI789',
                'tipo' => 'camioneta',
                'descripcion' => 'Ford Explorer 2021 Negro',
            ],
            [
                'cliente_id' => 3,
                'matricula' => null, // Para moto
                'tipo' => 'moto',
                'descripcion' => 'Yamaha FZ16 2022 Azul',
            ],
            [
                'cliente_id' => 4,
                'matricula' => 'JKL012',
                'tipo' => 'auto_mediano',
                'descripcion' => 'Renault Logan 2018 Gris',
            ]
        ];

        $vehiculos_creados = [];
        foreach ($vehiculos as $vehiculoData) {
            $vehiculos_creados[] = Vehiculo::create($vehiculoData);
        }

        // Crear productos automotrices
        $productosAutomotrices = [
            [
                'codigo' => 'ACEI001',
                'nombre' => 'Aceite Motor 15W40',
                'descripcion' => 'Aceite para motor multigrado',
                'precio_venta' => 35000,
                'stock' => 25,
                'activo' => true,
            ],
            [
                'codigo' => 'FILT001',
                'nombre' => 'Filtro de Aceite',
                'descripcion' => 'Filtro de aceite universal',
                'precio_venta' => 18000,
                'stock' => 15,
                'activo' => true,
            ],
            [
                'codigo' => 'CERA001',
                'nombre' => 'Cera para Auto',
                'descripcion' => 'Cera protectora para carrocería',
                'precio_venta' => 25000,
                'stock' => 8,
                'activo' => true,
            ],
            [
                'codigo' => 'LIMP001',
                'nombre' => 'Limpiador de Llantas',
                'descripcion' => 'Producto especializado para llantas',
                'precio_venta' => 15000,
                'stock' => 12,
                'activo' => true,
            ]
        ];

        foreach ($productosAutomotrices as $productoData) {
            ProductoAutomotriz::create($productoData);
        }

        // Crear productos de despensa
        $productosDespensa = [
            [
                'nombre' => 'Gaseosa Coca Cola',
                'descripcion' => 'Bebida gaseosa 350ml',
                'precio_venta' => 2500,
                'stock' => 30,
                'activo' => true,
            ],
            [
                'nombre' => 'Agua Embotellada',
                'descripcion' => 'Agua pura 500ml',
                'precio_venta' => 1500,
                'stock' => 40,
                'activo' => true,
            ],
            [
                'nombre' => 'Papas Fritas',
                'descripcion' => 'Snack papas fritas 100g',
                'precio_venta' => 3000,
                'stock' => 20,
                'activo' => true,
            ],
            [
                'nombre' => 'Chicles',
                'descripcion' => 'Chicles surtidos pack x5',
                'precio_venta' => 2000,
                'stock' => 25,
                'activo' => true,
            ]
        ];

        foreach ($productosDespensa as $productoData) {
            ProductoDespensa::create($productoData);
        }

        // Crear proveedores
        $proveedores = [
            [
                'nombre' => 'Distribuidora Automotriz SAC',
                'email' => 'ventas@autoparts.com',
                'telefono' => '555-2001',
                'descripcion' => 'Proveedor de repuestos y aceites',
                'deuda_pendiente' => 150000,
            ],
            [
                'nombre' => 'Productos de Limpieza El Sol',
                'email' => 'pedidos@elsol.com',
                'telefono' => '555-2002',
                'descripcion' => 'Productos de limpieza y cuidado automotriz',
                'deuda_pendiente' => 75000,
            ],
            [
                'nombre' => 'Distribuidora de Bebidas Norte',
                'email' => 'info@bebidasnorte.com',
                'telefono' => '555-2003',
                'descripcion' => 'Bebidas y snacks para punto de venta',
                'deuda_pendiente' => 0,
            ]
        ];

        foreach ($proveedores as $proveedorData) {
            Proveedor::create($proveedorData);
        }

        // Crear lavados (solo campos que existen en la migración)
        $lavados = [
            [
                'vehiculo_id' => 1,
                'empleado_id' => 1,
                'fecha' => Carbon::today(),
                'tipo_lavado' => 'completo',
                'precio' => 8000,
                'pulverizado' => true,
            ],
            [
                'vehiculo_id' => 2,
                'empleado_id' => 2,
                'fecha' => Carbon::today(),
                'tipo_lavado' => 'solo_fuera',
                'precio' => 4000,
                'pulverizado' => false,
            ],
            [
                'vehiculo_id' => 3,
                'empleado_id' => 1,
                'fecha' => Carbon::yesterday(),
                'tipo_lavado' => 'completo',
                'precio' => 10000,
                'pulverizado' => true,
            ],
            [
                'vehiculo_id' => 4,
                'empleado_id' => 3,
                'fecha' => Carbon::yesterday(),
                'tipo_lavado' => 'completo',
                'precio' => 3000,
                'pulverizado' => false,
            ]
        ];

        foreach ($lavados as $lavadoData) {
            Lavado::create($lavadoData);
        }

        // Crear ventas de productos automotrices
        $ventasAutomotrices = [
            [
                'producto_id' => 1, // Aceite Motor 15W40
                'cliente_id' => 1,
                'cantidad' => 1,
                'precio_unitario' => 35000,
                'total' => 35000,
                'fecha' => Carbon::today(),
            ],
            [
                'producto_id' => 2, // Filtro de Aceite
                'cliente_id' => 2,
                'cantidad' => 2,
                'precio_unitario' => 18000,
                'total' => 36000,
                'fecha' => Carbon::yesterday(),
            ]
        ];

        $ventasAutomotricesCreadas = [];
        foreach ($ventasAutomotrices as $ventaData) {
            $ventasAutomotricesCreadas[] = VentaProductoAutomotriz::create($ventaData);
        }

        // Crear ventas de productos de despensa
        $ventasDespensa = [
            [
                'producto_id' => 1, // Gaseosa Coca Cola
                'cliente_id' => 3,
                'cantidad' => 2,
                'precio_unitario' => 2500,
                'total' => 5000,
                'fecha' => Carbon::yesterday(),
            ],
            [
                'producto_id' => 3, // Papas Fritas
                'cliente_id' => 4,
                'cantidad' => 1,
                'precio_unitario' => 3000,
                'total' => 3000,
                'fecha' => Carbon::today()->subDays(2),
            ]
        ];

        $ventasDespensaCreadas = [];
        foreach ($ventasDespensa as $ventaData) {
            $ventasDespensaCreadas[] = VentaProductoDespensa::create($ventaData);
        }

        // Crear pagos a proveedores
        $pagosProveedores = [
            [
                'proveedor_id' => 1, // Distribuidora Automotriz SAC
                'monto' => 75000,
                'fecha' => Carbon::today()->subDays(5),
                'descripcion' => 'Pago parcial de aceites y filtros',
            ],
            [
                'proveedor_id' => 2, // Productos de Limpieza El Sol
                'monto' => 50000,
                'fecha' => Carbon::today()->subDays(3),
                'descripcion' => 'Pago productos de limpieza mes anterior',
            ]
        ];

        $pagosProveedoresCreados = [];
        foreach ($pagosProveedores as $pagoData) {
            $pagosProveedoresCreados[] = PagoProveedor::create($pagoData);
        }

        // Crear ingresos (solo campos que existen en la migración)
        $ingresos = [
            [
                'descripcion' => 'Lavado completo Toyota Corolla',
                'monto' => 8000,
                'fecha' => Carbon::today(),
                'tipo' => 'lavado',
                'referencia_id' => 1, // ID del lavado
            ],
            [
                'descripcion' => 'Venta aceite motor 15W40',
                'monto' => 35000,
                'fecha' => Carbon::today(),
                'tipo' => 'producto_automotriz',
                'referencia_id' => 1, // ID de la venta_producto_automotriz
            ],
            [
                'descripcion' => 'Venta bebidas y snacks',
                'monto' => 5000,
                'fecha' => Carbon::yesterday(),
                'tipo' => 'producto_despensa',
                'referencia_id' => 1, // ID de la venta_producto_despensa
            ],
            [
                'descripcion' => 'Lavado camioneta Ford Explorer',
                'monto' => 10000,
                'fecha' => Carbon::yesterday(),
                'tipo' => 'lavado',
                'referencia_id' => 3, // ID del lavado
            ],
            [
                'descripcion' => 'Venta filtros de aceite',
                'monto' => 36000,
                'fecha' => Carbon::yesterday(),
                'tipo' => 'producto_automotriz',
                'referencia_id' => 2, // ID de la venta_producto_automotriz
            ]
        ];

        foreach ($ingresos as $ingresoData) {
            Ingreso::create($ingresoData);
        }

        // Crear gastos generales (solo campos que existen en la migración)
        $gastosGenerales = [
            [
                'nombre' => 'Servicios Públicos',
                'descripcion' => 'Pago de agua y electricidad',
                'monto' => 120000,
                'fecha' => Carbon::now()->startOfMonth(),
            ],
            [
                'nombre' => 'Arriendo Local',
                'descripcion' => 'Pago mensual del local',
                'monto' => 800000,
                'fecha' => Carbon::now()->startOfMonth(),
            ],
            [
                'nombre' => 'Productos de Limpieza',
                'descripcion' => 'Compra de champú y cera',
                'monto' => 85000,
                'fecha' => Carbon::today()->subDays(3),
            ]
        ];

        foreach ($gastosGenerales as $gastoData) {
            GastoGeneral::create($gastoData);
        }

        // Crear egresos (solo campos que existen en la migración)
        $egresos = [
            [
                'descripcion' => 'Pago quincenal Juan Carlos',
                'monto' => 200000,
                'fecha' => Carbon::today()->subDays(7),
                'tipo' => 'salario',
                'referencia_id' => 1, // ID del empleado
            ],
            [
                'descripcion' => 'Pago a proveedor productos limpieza',
                'monto' => 50000,
                'fecha' => Carbon::today()->subDays(3),
                'tipo' => 'proveedor',
                'referencia_id' => 2, // ID del pago_proveedor
            ],
            [
                'descripcion' => 'Servicios públicos mes actual',
                'monto' => 120000,
                'fecha' => Carbon::today()->subDays(2),
                'tipo' => 'gasto_general',
                'referencia_id' => 1, // ID del gasto general
            ],
            [
                'descripcion' => 'Pago a distribuidora automotriz',
                'monto' => 75000,
                'fecha' => Carbon::today()->subDays(5),
                'tipo' => 'proveedor',
                'referencia_id' => 1, // ID del pago_proveedor
            ]
        ];

        foreach ($egresos as $egresoData) {
            Egreso::create($egresoData);
        }

        $this->command->info('Datos de demostración creados exitosamente!');
        $this->command->info('Usuario admin: admin@admin.com / password123');
    }
}
