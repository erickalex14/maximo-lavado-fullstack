<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TestAuth extends Command
{
    protected $signature = 'test:auth';
    protected $description = 'Test authentication setup';

    public function handle()
    {
        $this->info('Testing authentication setup...');
        
        // 1. Verificar usuario de prueba
        $user = User::where('email', 'admin@lavado.com')->first();
        if (!$user) {
            $this->warn('Creating test user...');
            $user = User::create([
                'name' => 'Administrador',
                'email' => 'admin@lavado.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]);
            $this->info('Test user created: admin@lavado.com / password123');
        } else {
            $this->info('Test user exists: admin@lavado.com / password123');
        }
        
        // 2. Verificar tabla sessions
        $sessionsCount = \DB::table('sessions')->count();
        $this->info("Sessions in database: {$sessionsCount}");
        
        // 3. Verificar configuración
        $this->info('Session driver: ' . config('session.driver'));
        $this->info('Session lifetime: ' . config('session.lifetime') . ' minutes');
        $this->info('Session table: ' . config('session.table'));
        
        $this->info('✅ Authentication setup complete!');
        $this->info('🔗 Test URLs:');
        $this->info('   - Create user: http://localhost:8000/create-default-user');
        $this->info('   - Login: http://localhost:8000/login');
        $this->info('   - Test auth: http://localhost:8000/test-auth');
        $this->info('   - Dashboard: http://localhost:8000/dashboard');
        
        return Command::SUCCESS;
    }
}
