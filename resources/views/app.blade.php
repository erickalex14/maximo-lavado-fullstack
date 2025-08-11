<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Favicon / App Icons -->
    <link rel="icon" type="image/png" href="/images/maximo-lavado-logo.png">
    <link rel="apple-touch-icon" href="/images/maximo-lavado-logo.png">
    <meta name="theme-color" content="#15803d">
    
    <title>{{ config('app.name', 'Máximo Lavado') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.ts'])
</head>
<body class="font-sans antialiased bg-gray-100">
    <div id="app"></div>
</body>
</html>
