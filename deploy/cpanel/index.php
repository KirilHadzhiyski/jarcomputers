<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// jarbl.bg is an addon domain at /home/ACCOUNT/public_html/jarbl.bg.
// Keep the Laravel application outside the public tree at /home/ACCOUNT/jar-app.
$appRoot = dirname(__DIR__, 2).'/jar-app';
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
