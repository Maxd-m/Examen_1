<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../core/Router.php';
// require_once '../domains/qr_code/resource/QRcode_resource.php';
require_once '../domains/password/resource/PasswordResource.php';



$scriptName = dirname($_SERVER['SCRIPT_NAME']);
$basePath = $scriptName;

$router = new Router('v1', $basePath);
// $userResource = new UserResource();
// $qrCodeResource = new QRCodeResource();
$passwordResource = new PasswordResource();
// rutas
// $router->addRoute('POST', '/qr/gen', [$qrCodeResource, 'generate']);
$router->addRoute('GET', '/password', [$passwordResource, 'generate']);
$router->addRoute('POST', '/password/validate', [$passwordResource, 'validate']);
$router->addRoute('POST', '/passwords', [$passwordResource, 'gen_mul']);


$router->dispatch();
?>