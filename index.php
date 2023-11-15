<?php

require 'vendor/autoload.php';

use ImageProxyPHP\Router;
use ImageProxyPHP\UrlValidator;
use ImageProxyPHP\S3ClientWrapper;
use ImageProxyPHP\ImageProxy;
use ImageProxyPHP\ServiceManager;

// Create a new ServiceManager instance
$serviceManager = new ServiceManager();

// Register services
$serviceManager->set('urlValidator', new UrlValidator());
$serviceManager->set('s3ClientWrapper', new S3ClientWrapper());
$serviceManager->set('imageProxy', new ImageProxy(
    $serviceManager->get('urlValidator'), 
    $serviceManager->get('s3ClientWrapper')
));

// Create a new Router instance
$router = new Router($serviceManager);

// Route the request
$router->route($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);