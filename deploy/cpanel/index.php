<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Default layout: /home/ACCOUNT/public_html next to /home/ACCOUNT/jar-app.
$appRoot = dirname(__DIR__).'/jar-app';
if (! is_file($appRoot.'/.env')) {
    http_response_code(503);
    header('Content-Type: text/plain; charset=UTF-8');
    exit('Сайтът се подготвя. Моля, опитайте по-късно.');
}
if (file_exists($maintenance = $appRoot.'/storage/framework/maintenance.php')) {
    require $maintenance;
}
require $appRoot.'/vendor/autoload.php';
$app = require $appRoot.'/bootstrap/app.php';
$app->handleRequest(Request::capture());
