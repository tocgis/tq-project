<?php
$Dispatcher=Yaf\Dispatcher::getInstance();
/**
 * 定义module 路由
 * @var Yaf
 */
$route_file = __DIR__ . "/configs/routes.ini";
if (file_exists($route_file)) {
    $routes = new Yaf\Config\Ini($route_file);
    $Dispatcher->getRouter()->addConfig($routes->admin);
}
