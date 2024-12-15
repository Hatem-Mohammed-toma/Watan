<?php

define('LARAVEL_START', microtime(true));

// Register the Composer autoloader
require __DIR__.'/../vendor/autoload.php';

// Turn on maintenance mode if needed
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Bootstrap the application
$app = require_once __DIR__.'/../bootstrap/app.php';

// Handle the request
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$response->send();

$kernel->terminate($request, $response);
