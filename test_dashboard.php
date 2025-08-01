<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

echo "Testing Dashboard dependencies...\n";

try {
    // Test DashboardRepositoryInterface
    $dashboardRepo = $app->make(\App\Contracts\DashboardRepositoryInterface::class);
    echo "✅ DashboardRepository instance created successfully\n";
    
    // Test DashboardService
    $dashboardService = $app->make(\App\Services\DashboardService::class);
    echo "✅ DashboardService instance created successfully\n";
    
    // Test DashboardController
    $dashboardController = $app->make(\App\Http\Controllers\DashboardController::class);
    echo "✅ DashboardController instance created successfully\n";
    
    echo "✅ All Dashboard dependencies are correctly configured\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
