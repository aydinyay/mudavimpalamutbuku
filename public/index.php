<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = '/home/mudavimp/mudavimpalamutbuku/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require '/home/mudavimp/mudavimpalamutbuku/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once '/home/mudavimp/mudavimpalamutbuku/bootstrap/app.php';

$app->handleRequest(Request::capture());
