<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Carga Composer
require_once __DIR__ . '/../vendor/autoload.php';

require_once '../core/Router.php';
// require_once '../domains/qr_code/resource/QRcodeResource.php';
require_once '../domains/ShortURL/resource/ShortenerResource.php';
require_once '../domains/password/resource/PasswordResource.php';
// Importa la clase con namespace (QR)
use Maxim\Examen1\Domains\QrCode\Resource\QRCodeResource;


$scriptName = dirname($_SERVER['SCRIPT_NAME']);
$basePath = $scriptName;

$router = new Router('v1', $basePath);
// $userResource = new UserResource();
$passwordResource = new PasswordResource();
$qrCodeResource = new QRCodeResource();
$shortenerResource = new ShortenerResource();
// rutas
$router->addRoute('POST', '/qr', [$qrCodeResource, 'generateText']);
$router->addRoute('POST', '/qr/text', [$qrCodeResource, 'generateText']);
$router->addRoute('POST', '/qr/url', [$qrCodeResource, 'generateURL']);
$router->addRoute('POST', '/qr/wifi', [$qrCodeResource, 'generateWifi']);
$router->addRoute('POST', '/qr/coordinates', [$qrCodeResource, 'generateCordinates']);

$router->addRoute('GET', '/password', [$passwordResource, 'generate']);
$router->addRoute('POST', '/password/validate', [$passwordResource, 'validate']);
$router->addRoute('POST', '/passwords', [$passwordResource, 'gen_mul']);

$router->addRoute('POST', '/shorten', [$shortenerResource, 'shorten']);
$router->addRoute('GET', '/redirect/{short_url}', [$shortenerResource, 'redirect']);
$router->addRoute('GET', '/stats/{short_url}', [$shortenerResource, 'stats']);


$router->dispatch();
?>